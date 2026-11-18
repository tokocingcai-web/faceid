<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()->with('schoolClass')->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        return view('admin.users.index', [
            'users' => $query->paginate(20)->withQueryString(),
            'roles' => User::query()->distinct()->orderBy('role')->pluck('role'),
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.users.create', [
            'user' => new User,
            'classes' => $this->classes(),
            'roles' => $this->roleOptions($request->user()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules($request));

        $photo = $request->file('photo')?->store('photos', 'public');

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'nis' => $validated['nis'] ?? null,
            'nip' => $validated['nip'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'photo' => $photo,
            'is_active' => $request->boolean('is_active'),
            'class_id' => $validated['class_id'] ?? null,
            'password' => $validated['password'],
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(Request $request, User $user): View
    {
        $this->authorizeUser($request->user(), $user);

        return view('admin.users.edit', [
            'user' => $user,
            'classes' => $this->classes(),
            'roles' => $this->roleOptions($request->user()),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorizeUser($request->user(), $user);

        $validated = $request->validate($this->rules($request, $user));

        $photo = $user->photo;

        if ($request->hasFile('photo')) {
            if ($photo) {
                Storage::disk('public')->delete($photo);
            }

            $photo = $request->file('photo')->store('photos', 'public');
        }

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'nis' => $validated['nis'] ?? null,
            'nip' => $validated['nip'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'photo' => $photo,
            'is_active' => $request->boolean('is_active'),
            'class_id' => $validated['class_id'] ?? null,
        ]);

        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->authorizeUser($request->user(), $user);
        abort_if($user->id === $request->user()->id, 403, 'Tidak dapat menghapus akun sendiri.');
        abort_if($user->role === 'dev', 403, 'Akun developer tidak dapat dihapus.');

        if ($user->photo) {
            Storage::disk('public')->delete($user->photo);
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Pengguna berhasil dihapus.');
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $this->authorizeUser($request->user(), $user);

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update(['password' => $validated['password']]);

        return back()->with('status', 'Password pengguna berhasil direset.');
    }

    public function importForm(Request $request): View
    {
        return view('admin.users.import');
    }

    public function importStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:4096'],
        ]);

        $handle = fopen($validated['file']->getRealPath(), 'r');
        $header = array_map('trim', fgetcsv($handle) ?: []);
        $created = 0;
        $errors = [];
        $line = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $line++;

            if (count(array_filter($row, fn ($value) => $value !== null && $value !== '')) === 0) {
                continue;
            }

            $data = array_combine($header, array_pad($row, count($header), null));
            $data = array_map(fn ($value) => is_string($value) ? trim($value) : $value, $data);

            $classId = null;
            if (! empty($data['class'])) {
                $classId = SchoolClass::query()->where('name', $data['class'])->value('id');
            }

            $payload = [
                'name' => $data['name'] ?? '',
                'email' => $data['email'] ?? '',
                'role' => $data['role'] ?? 'student',
                'nis' => $data['nis'] ?? null,
                'nip' => $data['nip'] ?? null,
                'gender' => $data['gender'] ?? null,
                'phone' => $data['phone'] ?? null,
                'class_id' => $classId,
                'password' => ($data['password'] ?? null) ?: 'password',
                'is_active' => true,
            ];

            $validator = Validator::make($payload, $this->rules($request, null, true));

            if ($validator->fails()) {
                $errors[] = "Baris {$line}: ".implode(' ', $validator->errors()->all());

                continue;
            }

            try {
                DB::transaction(function () use ($payload, &$created) {
                    User::create($payload);
                    $created++;
                });
            } catch (\Throwable $exception) {
                $errors[] = "Baris {$line}: ".$exception->getMessage();
            }
        }

        fclose($handle);

        return redirect()
            ->route('admin.users.index')
            ->with('status', "Import selesai. {$created} pengguna ditambahkan.")
            ->with('import_errors', $errors);
    }

    public function template(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['name', 'email', 'role', 'nis', 'nip', 'class', 'gender', 'phone', 'password']);
            fputcsv($handle, ['Budi Santoso', 'budi@example.com', 'student', '1001', '', 'X IPA 1', 'male', '08123456789', 'password']);
            fclose($handle);
        }, 'template-import-pengguna.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(Request $request, ?User $user = null, bool $import = false): array
    {
        $roleRule = $import
            ? Rule::in(['guru', 'student', 'wali'])
            : Rule::in($this->roleOptions($request->user()));

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'role' => ['required', $roleRule],
            'nis' => ['nullable', 'string', 'max:50', Rule::unique('users', 'nis')->ignore($user?->id)],
            'nip' => ['nullable', 'string', 'max:50', Rule::unique('users', 'nip')->ignore($user?->id)],
            'gender' => ['nullable', 'in:male,female'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'class_id' => ['nullable', 'exists:classes,id', 'required_if:role,student'],
            'password' => $import
                ? ['required', 'string', 'min:8']
                : [$user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<int, string>
     */
    private function roleOptions(User $actor): array
    {
        return $actor->role === 'dev'
            ? ['dev', 'admin', 'guru', 'student', 'wali']
            : ['guru', 'student', 'wali'];
    }

    private function authorizeUser(User $actor, User $target): void
    {
        if ($actor->role === 'dev') {
            return;
        }

        abort_if(
            in_array($target->role, ['dev', 'admin'], true),
            403,
            'Akun developer dan admin hanya dapat dikelola oleh developer.'
        );
    }

    private function classes()
    {
        return SchoolClass::query()->with('academicYear')->orderBy('name')->get();
    }
}
