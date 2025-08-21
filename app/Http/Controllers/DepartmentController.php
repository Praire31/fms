<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Audit;

class DepartmentController extends Controller
{
    // Add new department
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:departments,name',
        ]);

        $department = Department::create([
            'name' => $request->name,
        ]);

        // 🔥 Log audit
        Audit::create([
            'user_id'    => auth()->id(),
            'role'       => auth()->user()->getRoleNames()->implode(', '),
            'action'     => 'Create',
            'target'     => 'Department: ' . $department->name,
            'ip_address' => request()->ip(),
            'description'=> 'Created a department',
        ]);


        // Redirect back to Departments tab
        return redirect()->route('admin.dashboard', ['tab' => 'departments'])
                         ->with('success', 'Department added successfully!');
    }

    // Update existing department
    public function update(Request $request, $id)
    {
        $department = Department::findOrFail($id);

        $request->validate([
            'name' => 'required|unique:departments,name,' . $department->id
        ]);

        $department->update([
            'name' => $request->name
        ]);

        // 🔥 Log audit
       Audit::create([
            'user_id'    => auth()->id(),
            'role'       => auth()->user()->getRoleNames()->implode(', '),
            'action'     => 'Update',
            'target'     => 'Department: ' . $department->name,
            'ip_address' => request()->ip(),
            'description'=> 'Updated a department',
        ]);


        // Redirect back to Departments tab
        return redirect()->route('admin.dashboard', ['tab' => 'departments'])
                         ->with('success', 'Department updated successfully!');
    }

    // Delete department
    public function destroy($id)
    {
         // Check if the logged-in user has delete permission
    if (!auth()->user()->can('users.delete')) {
        return redirect()->route('admin.dashboard', ['tab' => 'users'])
                         ->with('error', '❌ Permission denied.');
    }

        $department = Department::findOrFail($id);
        $department->delete();

        // 🔥 Log audit
        Audit::create([
            'user_id'    => auth()->id(),
            'role'       => auth()->user()->getRoleNames()->implode(', '),
            'action'     => 'Delete',
            'target'     => 'Department: ' . $department->name,
            'ip_address' => request()->ip(),
            'description'=> 'Deleted a department',
        ]);


        // Redirect back to Departments tab
        return redirect()->route('admin.dashboard', ['tab' => 'departments'])
                         ->with('success', 'Department deleted successfully!');
    }
}
    