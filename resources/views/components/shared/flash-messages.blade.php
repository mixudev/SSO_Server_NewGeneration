@if (session('status'))
    <div class="container-xl pt-3">
        <div class="alert alert-success" role="status">{{ session('status') }}</div>
    </div>
@endif

@if (session('error'))
    <div class="container-xl pt-3">
        <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
    </div>
@endif
