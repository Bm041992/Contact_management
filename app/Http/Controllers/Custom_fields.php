<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CustomField;

class Custom_fields extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) 
        {
            return datatables()->of(CustomField::query()->orderByDesc('id'))
                ->addColumn('action', function ($row) {
                    $id=str_replace('=','',base64_encode($row->id));
                    return "<a href='javascript:void(0);' class='btn btn-sm btn-primary btn-edit' data-id='{{ $id }}'>Edit</a>
                    <a href='javascript:void(0);' class='btn btn-sm btn-danger btn-delete' data-id='{{ $id }}' '>Delete</a>";
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        else
        {
            return view('custom_fields.index');
        }
        // $customFields = CustomField::all();
        // return view('custom_fields.index', compact('customFields'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'field_type' => 'required|in:text,number,date,select',
            'is_required' => 'nullable|boolean',
        ]);

        $response = [
            'success' => false,
            'msg' => 'Something went wrong!',
        ];

        $customField = CustomField::create([
            'name' => $request->name,
            'field_type' => $request->field_type,
            'is_required' => $request->has('is_required'),
        ]);

        if ($customField) {
            $response['success'] = true;
            $response['msg'] = 'Custom field created successfully.';
            $response['data'] = $customField;
        }

        return response()->json($response);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request)
    {
        $id=base64_decode($request->id);
        $response = [
            'success' => false,
            'msg' => 'Something went wrong!'
        ];

        if($id)
        {
            $customField = CustomField::findOrFail($id); // Fetch the record
            if(!empty($customField))
            {
                $response['success'] = true;
                $response['msg'] = $customField;
            }
            else
            {
                $response['msg'] = 'Record not found!';
            }
        }
        else
        {
            $response['msg'] = 'Invalid request!';
        }
        return response()->json($response);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'field_type' => 'required|in:text,number,date',
            'is_required' => 'nullable|boolean',
        ]);

        $response = [
            'success' => false,
            'msg' => 'Something went wrong!',
        ];

        $customField = CustomField::findOrFail($id);
        $updated = $customField->update([
            'name' => $request->name,
            'field_type' => $request->field_type,
            'is_required' => $request->has('is_required'),
        ]);

        if ($updated) 
        {
            $response['success'] = true;
            $response['msg'] = 'Custom field updated successfully.';
            $response['data'] = $customField;
        }

        return response()->json($response);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $id=base64_decode($request->id);
        $response = [
            'success' => false,
            'msg' => 'Something went wrong!'
        ];

        if($id)
        {
            $customField = CustomField::findOrFail($id);
            
            $response['success']=false;
            $response['msg']='Something went wrong!';
            if($customField->delete())
            {
                $response['success']=true;
                $response['msg']='Custom field deleted successfully';
            }
            else
            {
                $response['msg']='Failed to delete custom field.';
            }
        }
        else
        {
            $response['msg']='Invalid request!';
        }
        echo json_encode($response);
        exit;
    }
}
