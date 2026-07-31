<?php
namespace App\Http\Controllers\backend;
use App\Http\Controllers\Controller;
use App\Models\Target;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class TargetController extends Controller {
    use AuthorizesRequests;
    public function index() { $this->authorize('read', Target::class); return view('backend.targets.index', ['targets' => Target::orderBy('target_name')->get()]); }
    public function create() { $this->authorize('create', Target::class); return view('backend.targets.create'); }
    public function store(Request $request) { $this->authorize('create', Target::class); Target::create($this->validated($request)); return redirect()->route('admin.targets.index')->with('success', 'Target created successfully.'); }
    public function edit(Target $target) { $this->authorize('edit', Target::class); return view('backend.targets.edit', compact('target')); }
    public function update(Request $request, Target $target) { $this->authorize('edit', Target::class); $target->update($this->validated($request, $target)); return redirect()->route('admin.targets.index')->with('success', 'Target updated successfully.'); }
    public function destroy(Target $target) { $this->authorize('delete', Target::class); $target->delete(); return redirect()->route('admin.targets.index')->with('success', 'Target deleted successfully.'); }
    private function validated(Request $request, ?Target $target = null): array { return $request->validate(['target_name' => ['required','string','max:255',Rule::unique('targets')->ignore($target?->id)], 'monthly_target' => 'required|integer|min:0', 'status' => 'required|boolean']); }
}
