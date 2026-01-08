<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SettingController extends Controller
{
    public function index(Request $request)
    {
        $query = Setting::query();
        
        if ($request->has('group')) {
            $query->where('group', $request->group);
        }
        
        if ($request->has('is_public')) {
            $query->where('is_public', $request->boolean('is_public'));
        }
        
        $settings = $query->orderBy('group', 'asc')
            ->orderBy('key', 'asc')
            ->paginate(50);
            
        return response()->json($settings);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|string|max:255|unique:settings',
            'value' => 'nullable|string',
            'type' => 'required|string|in:text,number,boolean,json',
            'group' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_public' => 'boolean',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $setting = Setting::create([
            'key' => $request->key,
            'value' => $request->value,
            'type' => $request->type,
            'group' => $request->group,
            'description' => $request->description,
            'is_public' => $request->is_public ?? false,
        ]);

        return response()->json([
            'message' => 'Setting created successfully',
            'setting' => $setting
        ], 201);
    }

    public function show(string $id)
    {
        $setting = Setting::findOrFail($id);
        return response()->json($setting);
    }

    public function update(Request $request, string $id)
    {
        $setting = Setting::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'key' => 'sometimes|string|max:255|unique:settings,key,' . $id,
            'value' => 'nullable|string',
            'type' => 'sometimes|string|in:text,number,boolean,json',
            'group' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'is_public' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $setting->update($request->all());

        return response()->json([
            'message' => 'Setting updated successfully',
            'setting' => $setting
        ]);
    }

    public function destroy(string $id)
    {
        $setting = Setting::findOrFail($id);
        $setting->delete();

        return response()->json(['message' => 'Setting deleted successfully']);
    }
    
    public function getByKey($key)
    {
        $setting = Setting::where('key', $key)->first();
        
        if (!$setting) {
            return response()->json(['message' => 'Setting not found'], 404);
        }
        
        return response()->json($setting);
    }
    
    public function getByGroup($group)
    {
        $settings = Setting::where('group', $group)
            ->orderBy('key', 'asc')
            ->get();
            
        return response()->json($settings);
    }

    public function getPublicByGroup($group)
    {
        try {
            $settings = Setting::where('group', $group)
                ->where('is_public', true)
                ->orderBy('key', 'asc')
                ->get();

            return response()->json($settings);
        } catch (\Exception $e) {
            // Table might not exist yet - return empty array
            return response()->json([]);
        }
    }
}
