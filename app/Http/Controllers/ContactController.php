<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        return Contact::create($request->all());
    }

    public function show($project_id, $id)
    {
        return Contact::where('id', $id)->first();
    }

    public function update(Request $request, $project_id, $id)
    {
        $contact = Contact::findOrFail($id);
        $contact->update($request->all());

        return $contact;
    }

    public function destroy($project_id, $id)
    {
        Contact::find($id)->delete();
        return ['message' => 'success'];
    }

}
