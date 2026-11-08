@extends('layouts.admin')

@section('content')
    <div class="admin-overview">
        <header class="admin-overview-header">
            <div>
                <p class="admin-overview-eyebrow">Community administration</p>
                <h1>Admin overview</h1>
                <p>Manage {{ get_setting('community_name') }} from one place.</p>
            </div>
            @can('cad_settings')
                <a class="admin-overview-primary-link" href="{{ route('admin.settings.general') }}">Community settings &rarr;</a>
            @endcan
        </header>

        <section aria-label="Community totals" class="admin-overview-stats">
            <div class="admin-overview-stat">
                <span>Active members</span>
                <strong>{{ number_format($membersCount) }}</strong>
                <small>Accounts with member status</small>
            </div>
            <div class="admin-overview-stat">
                <span>Applicant accounts</span>
                <strong>{{ number_format($pendingAccountsCount) }}</strong>
                <small>Accounts in applicant status</small>
            </div>
            <div class="admin-overview-stat">
                <span>Departments</span>
                <strong>{{ number_format($departmentsCount) }}</strong>
                <small>Configured departments</small>
            </div>
        </section>

        <section aria-labelledby="admin-overview-management-heading">
            <div class="admin-overview-section-heading">
                <h2 id="admin-overview-management-heading">Management</h2>
                <p>Choose an area to configure or review.</p>
            </div>
            <div class="admin-overview-grid">
                @can('user_access')
                    <a href="{{ route('admin.users.index') }}" class="admin-overview-tile">
                        <span class="admin-overview-tile-icon" aria-hidden="true">01</span>
                        <strong>Members</strong>
                        <span>View accounts, membership status and user records.</span>
                        <em>Open member management &rarr;</em>
                    </a>
                @endcan
                @can('department_manage')
                    <a href="{{ route('admin.department.index') }}" class="admin-overview-tile">
                        <span class="admin-overview-tile-icon" aria-hidden="true">02</span>
                        <strong>Departments</strong>
                        <span>Review and maintain departments and their details.</span>
                        <em>Manage departments &rarr;</em>
                    </a>
                @endcan
                @can('role_manage')
                    <a href="{{ route('admin.roles.index') }}" class="admin-overview-tile">
                        <span class="admin-overview-tile-icon" aria-hidden="true">03</span>
                        <strong>Roles &amp; permissions</strong>
                        <span>Maintain existing role definitions and access.</span>
                        <em>Manage roles &rarr;</em>
                    </a>
                @endcan
                @can('cad_settings')
                    <a href="{{ route('admin.settings.general') }}" class="admin-overview-tile">
                        <span class="admin-overview-tile-icon" aria-hidden="true">04</span>
                        <strong>Community settings</strong>
                        <span>Update branding, general preferences and integrations.</span>
                        <em>Open settings &rarr;</em>
                    </a>
                @endcan
                @can('license_type_manage')
                    <a href="{{ route('admin.license_type.index') }}" class="admin-overview-tile">
                        <span class="admin-overview-tile-icon" aria-hidden="true">05</span>
                        <strong>Licence types</strong>
                        <span>Manage licence definitions used by the CAD.</span>
                        <em>Manage licences &rarr;</em>
                    </a>
                @endcan
                @can('report_type_manage')
                    <a href="{{ route('admin.report_type.index') }}" class="admin-overview-tile">
                        <span class="admin-overview-tile-icon" aria-hidden="true">06</span>
                        <strong>Report types</strong>
                        <span>Maintain existing report categories.</span>
                        <em>Manage report types &rarr;</em>
                    </a>
                @endcan
            </div>
        </section>

        <div class="admin-overview-footer">
            <p>Looking for the CAD interface? <a href="{{ route('portal.dashboard') }}">Return to Member Portal &rarr;</a></p>
        </div>
    </div>
@endsection
