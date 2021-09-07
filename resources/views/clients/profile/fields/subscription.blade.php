<div class="card card-success">
    <div class="card-header">
        <h3 class="card-title">Suscripción Actual</h3>
    </div>
    <div class="card-body">
        <div class="form-group">
            <label class="form-label" for="start_at">Fecha de Inicio</label>
            <p class="form-control">{{$subscription->start_at->format('d/m/Y H:ia')}}</p>
        </div>
        <div class="form-group">
            <label class="form-label" for="finish_at">Fecha de finalización</label>
            <p class="form-control">{{$subscription->finish_at->format('d/m/Y H:ia')}}</p>
        </div>
        <div class="form-group">
            <label class="form-label" for="finish_at">Cantidad de tarjetas</label>
            <p class="form-control">{{$subscription->cards}}</p>
        </div>
    </div>
</div>