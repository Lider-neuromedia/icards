document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('allowed-accounts-app')) {
        initAllowedAccountsApp();
    }
});

function initAllowedAccountsApp() {
    const initialData = document.getElementById('allowed-accounts-data');
    const dataAllowed = JSON.parse(initialData.dataset.allowed || "[]")
        .map(x => parseInt(x));
    const dataAccounts = JSON.parse(initialData.dataset.accounts || "[]")
        .map((x) => {
            x.enabled = dataAllowed.includes(x.id);
            return x;
        });

    const app = new Vue({
        el: '#allowed-accounts-app',
        data: {
            accounts: dataAccounts,
        },
        computed: {
            enabledAccounts: function () {
                return this.accounts.filter(x => x.enabled);
            },
            disabledAccounts: function () {
                return this.accounts.filter(x => !x.enabled);
            },
        },
        methods: {
            enableAccount(account) {
                this.toggleAccount(account, true);
            },
            disableAccount(account) {
                this.toggleAccount(account, false);
            },
            toggleAccount(account, toggle) {
                const index = this.accounts.findIndex(x => x.id == account.id);
                this.accounts[index].enabled = toggle;
            }
        }
    });
}