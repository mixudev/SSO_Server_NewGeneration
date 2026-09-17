@extends('layouts.admin')

@section('content')
    <main class="container-xl py-4" id="main-content">
        <div class="page-header mb-4">
            <div>
                <h1 class="page-title">Mixu SSO Identity Platform</h1>
                <p class="text-secondary mb-0">Control plane foundation is operational.</p>
            </div>
        </div>

        <section aria-labelledby="platform-summary-heading">
            <h2 class="visually-hidden" id="platform-summary-heading">Platform summary</h2>
            <div class="row row-cards">
                @foreach ([['Foundation', 'Ready'], ['Authorization', 'RBAC'], ['OAuth2', 'Passport'], ['Recovery', 'Configured']] as [$label, $value])
                    <div class="col-sm-6 col-lg-3">
                        <div class="card card-sm h-100">
                            <div class="card-body">
                                <div class="text-secondary">{{ $label }}</div>
                                <div class="h2 mb-0">{{ $value }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <div class="row row-cards mt-4">
            <section class="col-lg-6" aria-labelledby="security-posture-heading">
                <div class="card h-100">
                    <div class="card-header"><h2 class="card-title" id="security-posture-heading">Security posture</h2></div>
                    <div class="card-body"><p class="mb-0">No live security metrics are connected to this overview yet.</p></div>
                </div>
            </section>
            <section class="col-lg-6" aria-labelledby="recent-activity-heading">
                <div class="card h-100">
                    <div class="card-header"><h2 class="card-title" id="recent-activity-heading">Recent activity</h2></div>
                    <div class="card-body"><p class="text-secondary mb-0">Activity will appear after the audit query is implemented.</p></div>
                </div>
            </section>
        </div>
    </main>
@endsection
