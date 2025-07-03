<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' - ' . config('app.name') : config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen font-sans antialiased bg-base-200">

    {{-- NAVBAR mobile only --}}
    <x-nav sticky class="lg:hidden">
        <x-slot:brand>
            <x-app-brand />
        </x-slot:brand>
        <x-slot:actions>
            <label for="main-drawer" class="lg:hidden me-3">
                <x-icon name="o-bars-3" class="cursor-pointer" />
            </label>
        </x-slot:actions>
    </x-nav>

    {{-- MAIN --}}
    <x-main>
    {{-- SIDEBAR --}}
    <x-slot:sidebar drawer="main-drawer" collapsible class="bg-base-100 lg:bg-inherit">
        
        {{-- BRAND --}}
        <x-app-brand class="px-5 pt-4" />

        <x-menu-separator />

        {{-- MENU --}}
        <x-menu activate-by-route>
            @if ($user = auth()->user())

                {{-- Always Visible --}}
                <x-menu-item title="Dashboard" icon="o-home" link="/dashboard" />
                <x-menu-item title="Profile" icon="o-user" link="/profile" />

                {{-- If email is verified --}}
                @if($user->hasVerifiedEmail())

                    {{-- Admin Menu --}}
                    @role('admin')
                        <x-menu-sub title="Administration" icon="o-cog">
                            <x-menu-item title="Users" icon="o-users" link="/admin/users" />
                            <x-menu-item title="Roles" icon="o-user-group" link="/admin/roles" />
                            <x-menu-item title="Permissions" icon="o-key" link="/admin/permissions" />
                        </x-menu-sub>
                    @endrole

                    {{-- HR Menu --}}
                    @role('hr')
                        <x-menu-item title="Job Post" icon="o-briefcase" link="/hr/jobpost" />
                    @endrole

                {{-- If not verified --}}
                @else
                    <div class="p-4 mt-2 text-sm bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700">
                        <p>Please verify your email to access all features.</p>
                        <a href="{{ route('verification.notice') }}" class="text-blue-600 hover:underline">Verify Now</a>
                    </div>
                @endif

                <x-menu-separator />

                {{-- User Info and Actions --}}
                <x-list-item :item="$user" value="name" sub-value="email" no-separator no-hover class="-mx-2 !-my-2 rounded">
                    <x-slot:actions>
                        <div class="flex items-center gap-2">
                            <x-theme-toggle class="btn btn-circle btn-ghost btn-sm" />
                            <x-button icon="o-arrow-right-start-on-rectangle" class="btn-circle btn-ghost btn-xs"
                                tooltip-left="Log-out" no-wire-navigate link="/logout" />
                        </div>
                    </x-slot:actions>
                </x-list-item>

            @endif
        </x-menu>
    </x-slot:sidebar>

    {{-- Content --}}
    <x-slot:content>
        {{ $slot }}
    </x-slot:content>
</x-main>


    {{--  TOAST area --}}
    <x-toast />

</body>

</html>
