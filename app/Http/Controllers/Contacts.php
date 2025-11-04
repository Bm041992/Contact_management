<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\Contact;
use App\Models\CustomField;
use App\Models\ContactCustomFieldValue;

class Contacts extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) 
        {
            // $contacts = Contact::query()->with('custom_field_values.custom_field', 'emails', 'phones')->where('is_active', 1)->orderByDesc('id');
            $contacts = Contact::query()->with([
                            'custom_field_values.custom_field',
                            'emails' => function ($query) {
                                $query->orderBy('email', 'asc'); 
                            },
                            'phones' => function ($query) {
                                $query->orderBy('phone', 'asc'); 
                            },
                        ])->where('is_active', 1)->orderByDesc('id');

            return DataTables::of($contacts)
                ->addColumn('gender', function ($row) {
                    return ucfirst($row->gender);
                })
                ->filterColumn('gender', function ($query, $keyword) {
                    $query->where('gender', 'like', "%{$keyword}%");
                })
                ->addColumn('emails', function ($contact) {
                    if ($contact->emails->isEmpty()) {
                        return '<em>No emails</em>';
                    }

                    $output = '';
                    foreach ($contact->emails as $email) {
                        $output .= htmlspecialchars($email->email) . '<br>';
                    }

                    return $output;
                })
                ->filterColumn('emails', function ($query, $keyword) {
                    $query->whereHas('emails', function ($q) use ($keyword) {
                        $q->where('email', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('phones', function ($contact) {
                    if ($contact->phones->isEmpty()) {
                        return '<em>No phones</em>';
                    }

                    $output = '';
                    foreach ($contact->phones as $phone) {
                        $output .= htmlspecialchars($phone->phone) . '<br>';
                    }

                    return $output;
                })
                ->filterColumn('phones', function ($query, $keyword) {
                    $query->whereHas('phones', function ($q) use ($keyword) {
                        $q->where('phone', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('custom_fields', function ($contact) {
                    if ($contact->custom_field_values->isEmpty()) {
                        return '<em>No custom fields</em>';
                    }

                    $output = '';
                    if(!empty($contact->custom_field_values))
                    {
                        foreach ($contact->custom_field_values as $cfv) 
                        {
                            if(!empty($cfv->custom_field->name) && !empty($cfv->value))
                            {
                                $output .= "<strong>{$cfv->custom_field->name}</strong>: {$cfv->value}<br>";
                            }
                        }
                    }

                    return $output;
                })
                ->filterColumn('custom_fields', function ($query, $keyword) {
                    $query->whereHas('custom_field_values', function ($q) use ($keyword) {
                        $q->where('value', 'like', "%{$keyword}%")
                        ->orWhereHas('custom_field', function ($q2) use ($keyword) {
                            $q2->where('name', 'like', "%{$keyword}%");
                        });
                    });
                })
                ->addColumn('action', function ($row) {
                    $id=str_replace('=','',base64_encode($row->id));
                    return "<a href='javascript:void(0);' class='btn btn-sm btn-primary btn-edit' data-id='{{ $id }}'>Edit</a>
                        <a href='javascript:void(0);' class='btn btn-sm btn-danger btn-delete' data-id='{{ $id }}' '>Delete</a>";
                })
                ->rawColumns(['gender', 'emails', 'phones','custom_fields', 'action'])
                ->make(true);
        }
        else
        {
            return view('contacts.index');
        }
    }

    public function getActiveContacts()
    {
        $contacts = Contact::where('is_active', 1)->get(['id', 'name']); 
        return response()->json($contacts);
    }

    public function customFields()
    {
        $fields = CustomField::orderBy('id', 'desc')->get();
        return response()->json($fields);
    }

    public function store(Request $request) 
    {
        $data = $request->validate([
            'name' => 'required',
            'gender' => 'nullable',
            'profile_image' => 'nullable|file|image',
            'additional_file' => 'nullable|file'
        ]);

        $response = [
            'success' => false,
            'msg' => 'Something went wrong!',
        ];
        if ($request->hasFile('profile_image')) 
        {
            $data['profile_image'] = $request->file('profile_image')->store('profiles', 'public');
        }
        if ($request->hasFile('additional_file')) 
        {
            $data['additional_file'] = $request->file('additional_file')->store('files', 'public');
        }

        $data['name'] = ucfirst(strtolower($data['name']));

        $contacts=Contact::create($data);
        if ($contacts) 
        {
            $emails = array_filter($request->email ?? []);
            foreach ($emails as $email) {
                $contacts->emails()->create(['email' => $email]);
            }

            $phones = array_filter($request->phone ?? []);
            foreach ($phones as $phone) {
                $contacts->phones()->create(['phone' => $phone]);
            }

            if ($request->has('custom_fields')) 
            {
                $custom_fields = array_filter($request->custom_fields ?? [], function ($value) {
                    return !is_null($value) && $value !== '';
                });
                foreach ($custom_fields as $custom_field_id => $value) 
                {
                    ContactCustomFieldValue::updateOrCreate(
                        ['contact_id' => $contacts->id, 'custom_field_id' => $custom_field_id],
                        ['value' => $value]
                    );
                }
            }
            $response['success'] = true;
            $response['msg'] = 'Contact created successfully.';
        }
        else
        {
            $response['msg'] = 'Failed to create contact.';
        }

        return response()->json($response);
    }

    public function edit(Request $request) 
    {
        $id=base64_decode($request->id);
        $response = [
            'success' => false,
            'msg' => 'Something went wrong!'
        ];

        if($id)
        {
            // $contact = Contact::findOrFail($id); // Fetch the record
            $contact = Contact::with(['emails', 'phones'])->findOrFail($id);
            if(!empty($contact))
            {
                $customFields = ContactCustomFieldValue::where('contact_id', $id)
                            ->pluck('value', 'custom_field_id')
                            ->toArray();
                            
                $contact['custom_fields'] = $customFields;
                $contact['profile_image_url'] = $contact->profile_image ? asset('storage/'.$contact->profile_image) : '';
                $contact['additional_file_url'] = $contact->additional_file ? asset('storage/'. $contact->additional_file) : '';
                $response['success'] = true;
                $response['msg'] = $contact;
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

    public function update(Request $request) 
    {
        $id = $request->id;
        if($id)
        {
            $contact = Contact::findOrFail($id);
            if($contact)
            {
                $data = $request->validate([
                    'name' => 'required',
                    'gender' => 'nullable',
                    'profile_image' => 'nullable|file|image',
                    'additional_file' => 'nullable|file'
                ]);

                if ($request->hasFile('profile_image')) {
                    $data['profile_image'] = $request->file('profile_image')->store('profiles', 'public');
                }
                if ($request->hasFile('additional_file')) {
                    $data['additional_file'] = $request->file('additional_file')->store('files', 'public');
                }

                $data['name'] = ucfirst(strtolower($data['name']));

                if($contact->update($data))
                {
                    $emails = array_filter($request->email ?? []); // remove empty values
                    $contact->emails()->delete(); // remove old ones
                    foreach ($emails as $email) {
                        $contact->emails()->create(['email' => $email]);
                    }

                    $phones = array_filter($request->phone ?? []);
                    $contact->phones()->delete();
                    foreach ($phones as $phone) {
                        $contact->phones()->create(['phone' => $phone]);
                    }

                    if ($request->has('custom_fields')) 
                    {
                        $custom_fields = array_filter($request->custom_fields ?? [], function ($value) {
                            return !is_null($value) && $value !== '';
                        });
                        foreach ($custom_fields as $custom_field_id => $value) 
                        {
                            ContactCustomFieldValue::updateOrCreate(
                                ['contact_id' => $contact->id, 'custom_field_id' => $custom_field_id],
                                ['value' => $value]
                            );
                        }
                    }
                    return response()->json(['success' => true, 'msg' => 'Contact updated successfully']);
                }
                else
                {
                    return response()->json(['success' => false, 'msg' => 'Failed to update contact']);
                }
            }
            else
            {
                return response()->json(['success' => false, 'msg' => 'Invalid request']);
            }
        }
        else
        {
            return response()->json(['success' => false, 'msg' => 'Invalid request']);
        }
    }

    public function destroy(Request $request) 
    {
        $id=base64_decode($request->id);
        $response = [
            'success' => false,
            'msg' => 'Something went wrong!'
        ];

        if($id)
        {
            $contact = Contact::findOrFail($id);
            
            $response['success']=false;
            $response['msg']='Something went wrong!';
            if($contact->delete())
            {
                $response['success']=true;
                $response['msg']='Contact deleted successfully';
            }
            else
            {
                $response['msg']='Failed to delete Contact';
            }
        }
        else
        {
            $response['msg']='Invalid request!';
        }
        echo json_encode($response);
        exit;
    }

    public function merge_contacts(Request $request)
    {
        $data = $request->validate([
            'master_contact_id' => 'required',
            'secondary_contact_id' => 'required|different:master_contact_id'
        ]);

        $response = [
            'success' => false,
            'msg' => 'Something went wrong!',
        ];

        $master    = Contact::with(['emails', 'phones', 'custom_field_values'])->find($data['master_contact_id']);
        $secondary = Contact::with(['emails', 'phones', 'custom_field_values'])->find($data['secondary_contact_id']);

        if(!empty($master) && !empty($secondary))
        {
            try {
                $masterEmails =$master->emails->pluck('email')->toArray();

                foreach ($secondary->emails as $email) {
                    if (!in_array(trim($email->email), $masterEmails)) {
                        $master->emails()->create([
                            'email' => $email->email
                        ]);
                    }
                }

                $masterPhones =$master->phones->pluck('phone')->toArray();
                foreach ($secondary->phones as $phone) {
                    if (!in_array(trim($phone->phone), $masterPhones)) {
                        $master->phones()->create([
                            'phone' => $phone->phone
                        ]);
                    }
                }

                $secondary->update(['is_active' => 0]);

                $response = [
                    'success' => true,
                    'msg' => 'Contacts merged successfully',
                ];
            }
            catch (\Exception $e) {
                $response['msg'] = 'Error: ' . $e->getMessage();
            }
        }

        return response()->json($response);
    }
}
