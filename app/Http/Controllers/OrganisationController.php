<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OrganisationController extends Controller
{
    public function getOrganisations()
    {
        $user = Auth::user();
        $organisations = $user->organisations;

        return response()->json([
            'status' => 'success',
            'message' => 'Organisations retrieved successfully',
            'data' => [
                'organisations' => $organisations,
            ],
        ]);
    }

    public function getOrganisation($orgId)
    {
        $user = Auth::user();
        $organisation = $user->$this->organisations()->where('orgId', $orgId)->first();

        if (!$organisation) {
            return response()->json([
                'status' => 'error',
                'message' => 'Organisation not found',
                'statusCode' => 404
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Organisation retrieved successfully',
            'data' => $organisation,
        ]);
    }

    public function createOrganisation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'string|nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();

        $organisation = Organisation::create([
            'orgId' => (string) Str::uuid(),
            'name' => $request->name,
            'description' => $request->description,
        ]);

        $user->$this->organisations()->attach($organisation->orgId);

        return response()->json([
            'status' => 'success',
            'message' => 'Organisation created successfully',
            'data' => $organisation,
        ], 201);
    }

    public function addUserToOrganisation(Request $request, $orgId)
    {
        $validator = Validator::make($request->all(), [
            'userId' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $organisation = Organisation::findOrFail($orgId);
        $user = User::findOrFail($request->userId);

        $organisation->users()->attach($user->id);

        return response()->json([
            'status' => 'success',
            'message' => 'User added to organisation successfully',
        ], 200);
    }
}
