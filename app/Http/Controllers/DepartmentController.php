<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;

class DepartmentController extends Controller
{
    // Add new department
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:departments,name',
        ]);

        Department::create([
            'name' => $request->name,
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

        // Redirect back to Departments tab
        return redirect()->route('admin.dashboard', ['tab' => 'departments'])
                         ->with('success', 'Department deleted successfully!');
    }
}
    