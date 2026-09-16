<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Display login form for the specified portal: mgmt, rinos, or sales.
     */
    public function index(Request $request, string $portal = 'mgmt')
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        $portalData = self::getPortalConfig($portal);
        $sliders = [];
        return view('backend.auth.login', compact('sliders'));

        return view('backend.auth.login', array_merge($portalData, compact('sliders')));
    }

    public function indexMgmt(Request $request)
    {
        return $this->index($request, 'mgmt');
    }

    public function indexRinos(Request $request)
    {
        return $this->index($request, 'rinos');
    }

    public function indexSales(Request $request)
    {
        return $this->index($request, 'sales');
    }

    /**
     * Handle authentication attempt with role-based portal validation.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::with('role')
            ->where('email', $request->email)
            ->first();

        if (!$user || !password_verify($request->password, $user->password)) {
            return back()->with([
                'error' => 'The provided credentials do not match our records.'
            ])->withErrors([
                'email' => 'The provided credentials do not match our records.'
            ])->onlyInput('email');
        }

        // Role deleted or missing
        if (!$user->role) {
            return back()->with([
                'error' => 'Your role has been removed. Please contact the administrator.'
            ])->withErrors([
                'email' => 'Your role has been removed. Please contact the administrator.'
            ])->onlyInput('email');
        }

        if($user->id != 1){
            if ($user->id != 1) {
                if ($user->role->status != 1) {
                    return back()->with([
                        'error' => 'Your role is inactive. Please contact the administrator.'
                    ])->withErrors([
                        'email' => 'Your role is inactive. Please contact the administrator.'
                    ])->onlyInput('email');
                }
            }
        }

        // Portal access enforcement
        $requestedPortal = $this->resolveRequestedPortal($request);
        $userPortal      = $user->getDesignatedPortal();

        if ($requestedPortal !== $userPortal) {
            $portalConfigs = [
                'mgmt'  => [
                    'label' => 'Management / Super Admin / Subadmin',
                    'url'   => self::getPortalRouteUrl('mgmt'),
                ],
                'rinos' => [
                    'label' => 'Recruiters',
                    'url'   => self::getPortalRouteUrl('rinos'),
                ],
                'sales' => [
                    'label' => 'Sales',
                    'url'   => self::getPortalRouteUrl('sales'),
                ],
            ];

            $requestedLabel = $portalConfigs[$requestedPortal]['label'] ?? ucfirst($requestedPortal);
            $userConfig     = $portalConfigs[$userPortal] ?? $portalConfigs['mgmt'];
            $userLabel      = $userConfig['label'];
            $userUrl        = $userConfig['url'];

            $errorMessage = "Access restricted: This portal is for <strong>{$requestedLabel}</strong> only. Your account belongs to <strong>{$userLabel}</strong>. Please use: <a href=\"{$userUrl}\" class=\"alert-link text-decoration-underline fw-bold\">{$userLabel} Login</a>.";
            $plainMessage = "Access restricted: Your account belongs to {$userLabel}. Please log in via {$userUrl}";

            return back()->with([
                'error' => $errorMessage
            ])->withErrors([
                'email' => $plainMessage
            ])->onlyInput('email');
        }

        Auth::login($user);

        $request->session()->regenerate();

        session(['locked' => false]);
        session()->flash('success', 'Login Successfully!');

        return redirect()->intended('admin/dashboard');
    }

    /**
     * Resolve which portal the login request originated from.
     */
    public function resolveRequestedPortal(Request $request): string
    {
        $portal = strtolower(trim((string) $request->input('portal')));
        if (in_array($portal, ['mgmt', 'rinos', 'sales'], true)) {
            return $portal;
        }

        $routeName = (string) $request->route()?->getName();
        if (str_contains($routeName, 'mgmt')) {
            return 'mgmt';
        }
        if (str_contains($routeName, 'rinos')) {
            return 'rinos';
        }
        if (str_contains($routeName, 'sales')) {
            return 'sales';
        }

        $path = strtolower(trim($request->path(), '/'));
        if (str_starts_with($path, 'mgmt')) {
            return 'mgmt';
        }
        if (str_starts_with($path, 'rinos')) {
            return 'rinos';
        }
        if (str_starts_with($path, 'sales')) {
            return 'sales';
        }

        $referer = strtolower((string) $request->header('referer'));
        if (str_contains($referer, '/rinos/login')) {
            return 'rinos';
        }
        if (str_contains($referer, '/sales/login')) {
            return 'sales';
        }
        if (str_contains($referer, '/mgmt/login')) {
            return 'mgmt';
        }

        return 'mgmt';
    }

    /**
     * Get safe route URL for a portal, with fallback for environments without url binding.
     */
    public static function getPortalRouteUrl(string $portal): string
    {
        $namedRoute = 'login.' . $portal;
        if (function_exists('app') && app()->bound('url')) {
            try {
                return route($namedRoute);
            } catch (\Throwable $e) {
                // fallback below
            }
        }
        return '/' . $portal . '/login';
    }

    /**
     * Get safe action route URL for a portal login check.
     */
    public static function getPortalCheckUrl(string $portal): string
    {
        $namedRoute = 'login.' . $portal . '.check';
        if (function_exists('app') && app()->bound('url')) {
            try {
                return route($namedRoute);
            } catch (\Throwable $e) {
                // fallback below
            }
        }
        return '/' . $portal . '/login';
    }

    /**
     * Get UI configuration data for a portal.
     */
    public static function getPortalConfig(string $portal): array
    {
        $configs = [
            'mgmt' => [
                'portal'         => 'mgmt',
                'portalTitle'    => 'Management Portal',
                'portalSubtitle' => 'Management • Super Admin • Subadmin',
                'portalBadge'    => 'Management Portal',
                'portalAction'   => self::getPortalCheckUrl('mgmt'),
            ],
            'rinos' => [
                'portal'         => 'rinos',
                'portalTitle'    => 'Recruiter Portal',
                'portalSubtitle' => 'Recruiters & Talent Acquisition',
                'portalBadge'    => 'Recruiter Portal (Rinos)',
                'portalAction'   => self::getPortalCheckUrl('rinos'),
            ],
            'sales' => [
                'portal'         => 'sales',
                'portalTitle'    => 'Sales Portal',
                'portalSubtitle' => 'Sales & Business Development',
                'portalBadge'    => 'Sales Portal',
                'portalAction'   => self::getPortalCheckUrl('sales'),
            ],
        ];

        return $configs[$portal] ?? $configs['mgmt'];
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        $portal = 'mgmt';
        if ($user && method_exists($user, 'getDesignatedPortal')) {
            $portal = $user->getDesignatedPortal();
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');

        $redirectRoute = match ($portal) {
            'rinos' => 'login.rinos',
            'sales' => 'login.sales',
            default => 'login.mgmt',
        };

        return redirect()->route($redirectRoute);
    }
}
