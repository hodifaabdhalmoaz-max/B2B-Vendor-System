<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    protected $settingService;

    public function __construct(SettingService $settingService)
    {
        $this->middleware('auth');
        $this->middleware('admin');
        $this->settingService = $settingService;
    }

    /**
     * Display a listing of the settings.
     */
    public function index()
    {
        $settings = Setting::orderBy('group')->orderBy('order')->get()->groupBy('group');
        
        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Show the form for editing the settings.
     */
    public function edit($group)
    {
        $settings = Setting::where('group', $group)->orderBy('order')->get();
        
        if ($settings->isEmpty()) {
            return redirect()->route('admin.settings.index')->with('error', __('messages.settings_group_not_found'));
        }
        
        return view('admin.settings.edit', compact('settings', 'group'));
    }

    /**
     * Update the settings in storage.
     */
    public function update(Request $request, $group)
    {
        $settings = Setting::where('group', $group)->get();
        
        foreach ($settings as $setting) {
            if ($request->has('settings.' . $setting->id)) {
                $value = $request->input('settings.' . $setting->id);
                
                // Handle file uploads
                if ($setting->type == 'file' && $request->hasFile('settings.' . $setting->id)) {
                    $file = $request->file('settings.' . $setting->id);
                    
                    // Delete old file if exists
                    if ($setting->value && Storage::exists('public/settings/' . $setting->value)) {
                        Storage::delete('public/settings/' . $setting->value);
                    }
                    
                    $fileName = time() . '_' . $file->getClientOriginalName();
                    $file->storeAs('public/settings', $fileName);
                    $value = $fileName;
                }
                
                // Handle checkbox
                if ($setting->type == 'checkbox') {
                    $value = $value ? '1' : '0';
                }
                
                $setting->value = $value;
                $setting->save();
            }
        }
        
        // Clear settings cache
        $this->settingService->clearCache();
        
        return redirect()->route('admin.settings.edit', $group)->with('success', __('messages.settings_updated'));
    }

    /**
     * Show the form for creating a new setting.
     */
    public function create()
    {
        return view('admin.settings.create');
    }

    /**
     * Store a newly created setting in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'key' => 'required|string|max:255|unique:settings',
            'display_name' => 'required|string|max:255',
            'type' => 'required|in:text,textarea,select,checkbox,file,number,email,password,color,date,time,datetime',
            'group' => 'required|string|max:255',
            'order' => 'required|integer|min:0',
            'options' => 'nullable|string',
            'description' => 'nullable|string',
            'is_translatable' => 'boolean',
        ]);
        
        $setting = new Setting();
        $setting->key = $request->key;
        $setting->value = $request->value;
        $setting->display_name = $request->display_name;
        $setting->type = $request->type;
        $setting->group = $request->group;
        $setting->order = $request->order;
        $setting->description = $request->description;
        $setting->is_translatable = $request->is_translatable ? true : false;
        
        // Handle options
        if ($request->type == 'select' && $request->has('options')) {
            $options = explode("\n", $request->options);
            $optionsArray = [];
            
            foreach ($options as $option) {
                $option = trim($option);
                if (!empty($option)) {
                    $parts = explode(':', $option, 2);
                    if (count($parts) == 2) {
                        $optionsArray[trim($parts[0])] = trim($parts[1]);
                    } else {
                        $optionsArray[$option] = $option;
                    }
                }
            }
            
            $setting->options = json_encode($optionsArray);
        }
        
        $setting->save();
        
        // Clear settings cache
        $this->settingService->clearCache();
        
        return redirect()->route('admin.settings.index')->with('success', __('messages.setting_created'));
    }

    /**
     * Show the form for editing the specified setting.
     */
    public function editSetting($id)
    {
        $setting = Setting::findOrFail($id);
        
        return view('admin.settings.edit-setting', compact('setting'));
    }

    /**
     * Update the specified setting in storage.
     */
    public function updateSetting(Request $request, $id)
    {
        $setting = Setting::findOrFail($id);
        
        $request->validate([
            'key' => 'required|string|max:255|unique:settings,key,' . $id,
            'display_name' => 'required|string|max:255',
            'type' => 'required|in:text,textarea,select,checkbox,file,number,email,password,color,date,time,datetime',
            'group' => 'required|string|max:255',
            'order' => 'required|integer|min:0',
            'options' => 'nullable|string',
            'description' => 'nullable|string',
            'is_translatable' => 'boolean',
        ]);
        
        $setting->key = $request->key;
        $setting->display_name = $request->display_name;
        $setting->type = $request->type;
        $setting->group = $request->group;
        $setting->order = $request->order;
        $setting->description = $request->description;
        $setting->is_translatable = $request->is_translatable ? true : false;
        
        // Handle value
        if ($request->has('value')) {
            // Handle file uploads
            if ($setting->type == 'file' && $request->hasFile('value')) {
                $file = $request->file('value');
                
                // Delete old file if exists
                if ($setting->value && Storage::exists('public/settings/' . $setting->value)) {
                    Storage::delete('public/settings/' . $setting->value);
                }
                
                $fileName = time() . '_' . $file->getClientOriginalName();
                $file->storeAs('public/settings', $fileName);
                $setting->value = $fileName;
            } else {
                // Handle checkbox
                if ($setting->type == 'checkbox') {
                    $setting->value = $request->value ? '1' : '0';
                } else {
                    $setting->value = $request->value;
                }
            }
        }
        
        // Handle options
        if ($request->type == 'select' && $request->has('options')) {
            $options = explode("\n", $request->options);
            $optionsArray = [];
            
            foreach ($options as $option) {
                $option = trim($option);
                if (!empty($option)) {
                    $parts = explode(':', $option, 2);
                    if (count($parts) == 2) {
                        $optionsArray[trim($parts[0])] = trim($parts[1]);
                    } else {
                        $optionsArray[$option] = $option;
                    }
                }
            }
            
            $setting->options = json_encode($optionsArray);
        }
        
        $setting->save();
        
        // Clear settings cache
        $this->settingService->clearCache();
        
        return redirect()->route('admin.settings.index')->with('success', __('messages.setting_updated'));
    }

    /**
     * Remove the specified setting from storage.
     */
    public function destroy($id)
    {
        $setting = Setting::findOrFail($id);
        
        // Delete file if exists
        if ($setting->type == 'file' && $setting->value && Storage::exists('public/settings/' . $setting->value)) {
            Storage::delete('public/settings/' . $setting->value);
        }
        
        $setting->delete();
        
        // Clear settings cache
        $this->settingService->clearCache();
        
        return redirect()->route('admin.settings.index')->with('success', __('messages.setting_deleted'));
    }
}
