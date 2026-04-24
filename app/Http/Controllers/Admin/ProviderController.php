<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\AuditService;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    private array $encryptedKeys = [
        'sms_zenvia_token',
        'sms_infobip_api_key',
        'sms_labsmobile_token',
        'email_zenvia_token',
        'email_infobip_api_key',
    ];

    public function index()
    {
        $settings = [];
        $keys = [
            'sms_driver', 'sms_zenvia_token', 'sms_zenvia_from', 'sms_zenvia_country',
            'sms_infobip_api_key', 'sms_infobip_base_url', 'sms_infobip_from',
            'sms_labsmobile_username', 'sms_labsmobile_token', 'sms_labsmobile_tpoa', 'sms_labsmobile_country',
            'email_driver', 'email_zenvia_token', 'email_zenvia_from_name', 'email_zenvia_from_address',
            'email_infobip_api_key', 'email_infobip_base_url', 'email_infobip_from_name', 'email_infobip_from_address',
        ];

        foreach ($keys as $key) {
            $val = Setting::get($key);
            // Mask secrets for display
            if (in_array($key, $this->encryptedKeys) && $val) {
                $settings[$key . '_set'] = true;
                $settings[$key] = '';   // don't expose decrypted value
            } else {
                $settings[$key] = $val ?? '';
            }
        }

        // Fallback to env if not set in DB
        $settings['sms_driver']                 = $settings['sms_driver']                 ?: config('services.sms.driver', 'log');
        $settings['sms_zenvia_from']             = $settings['sms_zenvia_from']             ?: config('services.sms.zenvia_from', 'CuponesHub');
        $settings['sms_zenvia_country']          = $settings['sms_zenvia_country']          ?: config('services.sms.zenvia_country', '57');
        $settings['sms_infobip_base_url']        = $settings['sms_infobip_base_url']        ?: config('services.sms.infobip_base_url', 'https://api.infobip.com');
        $settings['sms_labsmobile_tpoa']         = $settings['sms_labsmobile_tpoa']         ?: config('services.sms.labsmobile_tpoa', 'CuponesHub');
        $settings['sms_labsmobile_country']      = $settings['sms_labsmobile_country']      ?: config('services.sms.labsmobile_country', '57');
        $settings['email_driver']                = $settings['email_driver']                ?: config('services.email.driver', 'log');
        $settings['email_zenvia_from_name']      = $settings['email_zenvia_from_name']      ?: config('services.email.zenvia_from_name', 'CuponesHub');
        $settings['email_zenvia_from_address']   = $settings['email_zenvia_from_address']   ?: config('services.email.zenvia_from_address', '');
        $settings['email_infobip_base_url']      = $settings['email_infobip_base_url']      ?: config('services.email.infobip_base_url', 'https://api.infobip.com');
        $settings['email_infobip_from_name']     = $settings['email_infobip_from_name']     ?: config('services.email.infobip_from_name', 'CuponesHub');
        $settings['email_infobip_from_address']  = $settings['email_infobip_from_address']  ?: config('services.email.infobip_from_address', '');

        return view('admin.providers.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'sms_driver'                => 'required|in:log,zenvia,infobip,labsmobile',
            'sms_zenvia_token'          => 'nullable|string|max:200',
            'sms_zenvia_from'           => 'nullable|string|max:50',
            'sms_zenvia_country'        => 'nullable|string|max:5',
            'sms_infobip_api_key'       => 'nullable|string|max:200',
            'sms_infobip_base_url'      => 'nullable|url|max:200',
            'sms_infobip_from'          => 'nullable|string|max:50',
            'sms_labsmobile_username'   => 'nullable|string|max:100',
            'sms_labsmobile_token'      => 'nullable|string|max:200',
            'sms_labsmobile_tpoa'       => 'nullable|string|max:50',
            'sms_labsmobile_country'    => 'nullable|string|max:5',
            'email_driver'              => 'required|in:log,zenvia,infobip',
            'email_zenvia_token'        => 'nullable|string|max:200',
            'email_zenvia_from_name'    => 'nullable|string|max:100',
            'email_zenvia_from_address' => 'nullable|email|max:150',
            'email_infobip_api_key'     => 'nullable|string|max:200',
            'email_infobip_base_url'    => 'nullable|url|max:200',
            'email_infobip_from_name'   => 'nullable|string|max:100',
            'email_infobip_from_address'=> 'nullable|email|max:150',
        ]);

        $plainKeys   = array_diff(array_keys($data), $this->encryptedKeys);
        $secretKeys  = array_intersect(array_keys($data), $this->encryptedKeys);

        // Save plain settings
        foreach ($plainKeys as $key) {
            if ($data[$key] !== null && $data[$key] !== '') {
                Setting::set($key, $data[$key], false);
            }
        }

        // Save secrets — only update if a new value was provided (non-empty)
        foreach ($secretKeys as $key) {
            if (!empty($data[$key])) {
                Setting::set($key, $data[$key], true);
            }
        }

        AuditService::log('providers_updated', Setting::class, 0, [], [
            'sms_driver'   => $data['sms_driver'],
            'email_driver' => $data['email_driver'],
        ]);

        return back()->with('success', 'Configuración de proveedores guardada correctamente.');
    }
}
