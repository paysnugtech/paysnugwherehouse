<?php
namespace App\Http\Controllers\v1;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;

class DepartmentController extends Controller
{
    public function createDepartment(Request $request)
    {
        $validatedData = $request->validate([
            'dept_name' => 'required|string|max:255',
            'description' => 'required|string',
            'email' => 'required|string',
        ]);

        $department = Department::create($validatedData);

        return response()->json(['message' => 'Department created successfully', 'data' => $department]);
    }
}
