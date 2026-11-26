/**
 * Cloudflare Turnstile on the form brick: x-data="turnstile(siteKey)" on an element with wire:ignore.
 * The token goes to the component's turnstileToken without a request. After every check the server
 * dispatches "turnstile-reset", because a token can only be checked once.
 *
 * @see https://developers.cloudflare.com/turnstile/get-started/client-side-rendering/
 */
let scriptLoaded = null;

function loadScript() {
    scriptLoaded ??= new Promise((resolve, reject) => {
        window.onTurnstileLoad = resolve;

        const script = document.createElement('script');
        script.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit&onload=onTurnstileLoad';
        script.async = true;
        script.onerror = reject;
        document.head.append(script);
    });

    return scriptLoaded;
}

export function turnstile(siteKey) {
    return {
        widgetId: null,

        async init() {
            await loadScript();

            this.widgetId = window.turnstile.render(this.$el, {
                sitekey: siteKey,
                theme: 'auto',
                callback: (token) => this.$wire.set('turnstileToken', token, false),
                'expired-callback': () => this.$wire.set('turnstileToken', '', false),
                'error-callback': () => this.$wire.set('turnstileToken', '', false),
            });

            this.onReset = (event) => {
                if (event.detail?.id === this.$wire.$id && this.widgetId !== null) {
                    window.turnstile.reset(this.widgetId);
                }
            };

            window.addEventListener('turnstile-reset', this.onReset);
        },

        onReset: null,

        destroy() {
            window.removeEventListener('turnstile-reset', this.onReset);

            if (this.widgetId !== null) {
                window.turnstile.remove(this.widgetId);
            }
        },
    };
}
