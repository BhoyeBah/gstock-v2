@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mt-3 border-0 shadow-sm" role="alert">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Fermer">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mt-3 border-0 shadow-sm" role="alert">
        <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Fermer">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if(session('info'))
    <div class="alert alert-info alert-dismissible fade show mt-3 border-0 shadow-sm" role="alert">
        <i class="fas fa-info-circle"></i> {{ session('info') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Fermer">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mt-3 border-0 shadow-sm" role="alert">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li><i class="fas fa-times-circle"></i> {{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert" aria-label="Fermer">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif
