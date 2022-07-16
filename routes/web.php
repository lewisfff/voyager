<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    $maps = Storage::disk('cstrike')->allFiles('maps');
    $configs = [
        'timersurf.cfg',
        'combatsurf.cfg',
        'de_.cfg',
        'mg_.cfg',
    ];

    return view('welcome', ['files' => $maps, 'configs' => $configs]);
});

Route::post('/file.upload', function () {
    // request validation for upload
    $validator = Validator::make(request()->all(), [
        'file' => 'required|file|max:500000',
        'config' => 'required|max:2048',
    ]);

    // the config must exit in the $configs array
    $configs = Storage::disk('cstrike')->allFiles('/cfg/sourcemod/map-cfg');
    $configs = array_map(function ($file) {
        return basename($file);
    }, $configs);
    $validator->after(function ($validator) use ($configs) {
        if (!in_array(request('config'), $configs)) {
            $validator->errors()->add('config', 'Config file not found');
        }
    });

    // make sure a counter-strike: source map is uploaded
    $validator->after(function ($validator) {
        $file = request('file')->getPathName();
        $fp = fopen($file, 'rb');
        $header = fread($fp, 4);
        $version = fread($fp, 4);
        $version = unpack('V', $version);
        fclose($fp);

        $versions = [
            19 => 'CS:S pre-OB',
            20 => 'CS:S OB',
            21 => 'CSGO',
            22 => 'Dota 2',
            23 => 'Dota 2 Source 2',
            29 => 'Titanfall',
        ];

        if ($header === 'IBSP') {
            $validator->errors()->add('file', 'I just checked the header and the version, but it doesn\'t seem to be a counter-strike: source map. Is this a Quake 3 map?');
        } elseif ($header !== 'VBSP' || $version[1] != 19 && $version[1] != 20) {
            $validator->errors()->add('file', 'Invalid BSP file, For some reason you uploaded a ' . ($versions[$version[1]] ?? 'corrupted') . ' map. Please upload a map from ' . $versions[19] . ' or ' . $versions[20]);
        }
    });

    if ($validator->fails()) {
        return redirect()->back()->withInput()->withErrors($validator->errors());
    }

    $validator->validate();

    // upload the bsp file to cstrike/maps folder
    $file = request('file')->storeAs('maps', request('file')->getClientOriginalName(), 'cstrike');

    // create a bzip2 archive of the map file
//    $archive = new \PharData(storage_path('app/cstrike/maps/' . basename($file)));
//    $archive->compress(\Phar::GZ);
    

    // if the file was uploaded successfully, upload the config file to cstrike/cfg/sourcemod/map-cfg folder
    if ($file) {
        // create a copy of the request()->config file and point it to the name of the uploaded file, but with the extension .cfg
        // and place it in the cstrike/cfg/sourcemod/map-cfg folder
        $config = storage_path('app/cstrike/cfg/sourcemod/map-cfg/' . request('config'));
        $file = request('file')->getClientOriginalName();
        $name = str_replace('.bsp', '.cfg', $file);
        $link = storage_path('app/cstrike/cfg/sourcemod/map-cfg/' . $name);
        if (!file_exists($link)) {
            // copy the contents of the config file to the $link location
            $success = copy($config, $link);

            // if successful, return to the previous page with a success message
            if ($success) {
                return redirect()->back()->with('success', 'Map uploaded successfully');
            }
        }

        // if we get here, something went wrong, return to the previous page with an error message
        return redirect()->back()->with('error', 'Something went wrong while uploading the map');
    }

    // return back to the page with json response
    return response()->json([
        'success' => true,
        'message' => 'File uploaded successfully',
    ]);
})->name('file.upload');
