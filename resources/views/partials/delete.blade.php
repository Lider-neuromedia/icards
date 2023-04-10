@if (isset($tiny) && $tiny === true)
    <button type="button" class="btn btn-dark btn-sm" data-toggle="modal" data-target="#{{ $id_form }}Modal">
        <i class="fa fa-trash" aria-hidden="true"></i> Borrar
    </button>
@else
    <div class="card mb-3">
        <div class="card-header">
            <div class="card-title">{{ $label }}</div>
            <div class="card-tools">
                <button type="button" class="btn btn-danger btn-sm" data-toggle="modal"
                    data-target="#{{ $id_form }}Modal">
                    <i class="fa fa-trash" aria-hidden="true"></i>
                </button>
            </div>
        </div>
    </div>
@endif

<form id="{{ $id_form }}" action="{{ $route }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<!-- Modal -->
<div class="modal fade" id="{{ $id_form }}Modal" tabindex="-1" role="dialog"
    aria-labelledby="{{ $id_form }}ModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $id_form }}ModalLabel">Borrar</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                ¿Realmente desea borrar este registro?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary"
                    onclick="document.getElementById('{{ $id_form }}').submit();">
                    Borrar
                </button>
            </div>
        </div>
    </div>
</div>
