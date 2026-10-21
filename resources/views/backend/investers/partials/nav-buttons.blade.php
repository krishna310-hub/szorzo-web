<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <a href="{{ route('admin.investers.dashboard') }}"
                class="btn {{ request()->routeIs('admin.investers.dashboard') ? 'btn-primary' : 'btn-soft-primary' }}">
                <i class="ri-dashboard-2-line align-middle me-1"></i> Invester Dashboard
            </a>

            @can('read', \App\Models\Invester::class)
                <a href="{{ route('admin.investers.index') }}"
                    class="btn {{ request()->routeIs('admin.investers.index') || request()->routeIs('admin.investers.create') || request()->routeIs('admin.investers.edit') || request()->routeIs('admin.investers.show') ? 'btn-primary' : 'btn-soft-primary' }}">
                    <i class="ri-funds-line align-middle me-1"></i> Investers
                </a>
            @endcan

            @can('read', \App\Models\InvesterDeployment::class)
                <a href="{{ route('admin.invester-deployments.index') }}"
                    class="btn {{ request()->routeIs('admin.invester-deployments.*') ? 'btn-primary' : 'btn-soft-primary' }}">
                    <i class="ri-briefcase-4-line align-middle me-1"></i> Invest Modules & Profit Sharing
                </a>
            @endcan

            @can('read', \App\Models\InvesterTeamManagement::class)
                <a href="{{ route('admin.invester-team-managements.index') }}"
                    class="btn {{ request()->routeIs('admin.invester-team-managements.*') ? 'btn-primary' : 'btn-soft-primary' }}">
                    <i class="ri-team-line align-middle me-1"></i> Team & Management Scope
                </a>
            @endcan

            @can('read', \App\Models\InvesterFinancialDetail::class)
                <a href="{{ route('admin.invester-financial-details.index') }}"
                    class="btn {{ request()->routeIs('admin.invester-financial-details.*') ? 'btn-primary' : 'btn-soft-primary' }}">
                    <i class="ri-money-dollar-circle-line align-middle me-1"></i> Overall Investments
                </a>
            @endcan

            @can('read', \App\Models\InvesterLegalCompliance::class)
                <a href="{{ route('admin.invester-legal-compliances.index') }}"
                    class="btn {{ request()->routeIs('admin.invester-legal-compliances.*') ? 'btn-primary' : 'btn-soft-primary' }}">
                    <i class="ri-shield-check-line align-middle me-1"></i> Legal, Compliance & Governance
                </a>
            @endcan
        </div>
    </div>
</div>

