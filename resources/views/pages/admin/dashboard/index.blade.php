@extends('layouts.app')

@section('content')
    <main class="container py-4">
        <div class="page-header mb-4">
            <div>
                <h1 class="page-title">Mixu SSO Identity Platform</h1>
                <p class="text-secondary mb-0">Control plane foundation is operational.</p>
            </div>
        </div>

        <div class="row row-cards">
            <div class="col-md-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="text-secondary">Foundation</div>
                        <div class="h2 mb-0">Ready</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="text-secondary">Authorization</div>
                        <div class="h2 mb-0">RBAC</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="text-secondary">OAuth2</div>
                        <div class="h2 mb-0">Passport</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="text-secondary">Recovery</div>
                        <div class="h2 mb-0">Configured</div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
