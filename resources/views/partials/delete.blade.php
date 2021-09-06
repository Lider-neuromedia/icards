<div class="card mb-3">
    <div class="card-header">
        <div class="card-title">{{$label}}</div>

        <div class="card-tools">
            <a class="btn btn-danger btn-sm" title="Borrar" href="{{$route}}"
                onclick="event.preventDefault(); document.getElementById('{{$id_form}}').submit();">
                <i class="fa fa-trash" aria-hidden="true"></i>
            </a>
        </div>
    </div>

    <form id="{{$id_form}}" action="{{$route}}" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
</div>