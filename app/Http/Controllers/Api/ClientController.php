<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ClientResource;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class ClientController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', User::class);

        $trainer = auth()->user();
        $clients = $trainer->clients()->get();
        return ClientResource::collection($clients);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        $trainer = auth()->user();
        $client = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'role' => 'user',
            'trainer_id' => $trainer->id,
        ]);

        return new ClientResource($client);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $client)
    {
        $this->authorize('view', $client);

        return new ClientResource($client);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $client)
    {
        $this->authorize('update', $client);

        $request->validate([
            'name' => 'string|max:255',
            'email' => 'email|unique:users,email,' . $client->id . ',id',
            'password' => 'nullable|string|min:6',
        ]);

        $data = $request->only(['name', 'email']);
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->input('password'));
        }

        $client->update($data);
        return new ClientResource($client);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $client)
    {
        $this->authorize('delete', $client);

        $client->delete();
        return response(status: 204);
    }
}
