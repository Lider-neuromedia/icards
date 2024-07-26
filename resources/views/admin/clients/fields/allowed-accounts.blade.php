<div
    id="allowed-accounts-data"
    data-accounts='@json($accounts)'
    data-allowed='@json(old('allowed_accounts') ?: $allowedAccounts)'
>
    <div class="row" id="allowed-accounts-app">
        <div class="col-12 col-md-6">
            <h5>
                {{-- TODO: __() --}}
                Cuentas Habilitadas
                <span class="text-gray text-sm">(@{{ enabledAccounts.length }})</span>
            </h5>

            <input
                v-for="account in enabledAccounts"
                :key="account.id"
                type="hidden"
                name="allowed_accounts[]"
                :value="account.id"
            />

            <div v-if="enabledAccounts.length == 0" class="list-group">
                <div style="line-height: 1.1rem;" class="list-group-item list-group-item-action list-group-item-dark">
                    <div>No hay cuentas habilitadas</div>
                    <div><span class="text-gray text-sm"></span></div>
                </div>
            </div>

            <div class="list-group">
                <button
                    v-for="account in enabledAccounts"
                    :key="account.id"
                    type="button"
                    style="line-height: 1.1rem;"
                    class="list-group-item list-group-item-action"
                    @@click="disableAccount(account)"
                >
                    <div v-text="account.name"></div>
                    <div><span class="text-gray text-sm" v-text="account.email"></span></div>
                </button>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <h5>
                Cuentas Disponibles
                <span class="text-gray text-sm">(@{{ disabledAccounts.length }})</span>
            </h5>

            <div class="overflow-auto" style="max-height: 350px;">
                <div class="list-group">
                    <button
                        v-for="account in disabledAccounts"
                        :key="account.id"
                        type="button"
                        style="line-height: 1.1rem;"
                        class="list-group-item list-group-item-action"
                        @@click="enableAccount(account)"
                    >
                        <div v-text="account.name"></div>
                        <div><span class="text-gray text-sm" v-text="account.email"></span></div>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
