<nav class="settings-sidebar-nav" aria-label="Administration settings">
    <div class="settings-sidebar-heading">
        <div class="settings-sidebar-mark" aria-hidden="true">C</div>
        <div>
            <div class="settings-sidebar-name">CommunityCAD</div>
            <div class="settings-sidebar-caption">Administration</div>
        </div>
    </div>
    <a class="settings-sidebar-back" href="{{ route('admin.index') }}">&#8592; Back to admin</a>

    <div class="settings-sidebar-group">General</div>
    <a class="settings-sidebar-link {{ request()->is('admin/settings/general') ? 'is-active' : '' }}"
        href="{{ route('admin.settings.general') }}">General settings</a>
    <a class="settings-sidebar-link {{ request()->is('admin/settings/roleplay') ? 'is-active' : '' }}"
        href="{{ route('admin.settings.roleplay') }}">Roleplay</a>
    <a class="settings-sidebar-link {{ request()->is('admin/settings/application') ? 'is-active' : '' }}"
        href="{{ route('admin.settings.application') }}">Applications</a>
    <a class="settings-sidebar-link {{ request()->is('admin/settings/cad') ? 'is-active' : '' }}"
        href="{{ route('admin.settings.cad') }}">CAD / MDT</a>

    <a class="settings-sidebar-link {{ request()->is('admin/cad-audio*') ? 'is-active' : '' }}"
        href="{{ route('admin.cad_audio.index') }}">CAD Audio</a>

    <div class="settings-sidebar-group">Integrations</div>
    <a class="settings-sidebar-link {{ request()->is('admin/settings/discord_roles') ? 'is-active' : '' }}"
        href="{{ route('admin.settings.discord_roles') }}">Discord roles</a>
    <a class="settings-sidebar-link {{ request()->is('admin/settings/discord') ? 'is-active' : '' }}"
        href="{{ route('admin.settings.discord') }}">Discord logging</a>
    @if (in_array(auth()->user()->id, config('cad.owner_ids')))
        <a class="settings-sidebar-link {{ request()->is('admin/settings/api_key') ? 'is-active' : '' }}"
            href="{{ route('admin.settings.api_key') }}">API key</a>
    @endif
</nav>
