function when_dom_ready(callback)
{
    document.readyState === "interactive" || document.readyState === "complete" ? callback() : document.addEventListener("DOMContentLoaded", callback);
}

/**
 * method -> GET, POST…
 * url
 * onsuccess(request, json_data)
 * onerror(request, is_connection_error)
 */
function ajax_json_post_call(url, form_data, onsuccess, onerror)
{
    'use strict';

    var request = new XMLHttpRequest();
    request.open('POST', url, true);

    request.onload = function()
    {
        if (this.status >= 200 && this.status < 400)
        {
            onsuccess(this, JSON.parse(this.response));
        }
        else
        {
            onerror(this, false);
        }
    };

    request.onerror = function() {
        onerror(this, true)
    };

    request.send(form_data);
}


when_dom_ready(function()
{
    'use strict';

    var button = document.getElementById('form-mail-submit');
    var form = document.getElementById('form-mail');
    var email = document.getElementById('form-mail-email');

    var button_text = document.getElementById('button-mail-text');

    var icon_orig = document.getElementById('button-mail-icon-original');
    var icon_error = document.getElementById('button-mail-icon-error');
    var icon_success = document.getElementById('button-mail-icon-success');

    button.onclick = function(e)
    {
        self = button;
        e.preventDefault();

        if (button.classList.contains('is-loading') || email.value.trim() == '')
        {
            return;
        }

        self.classList.add('is-loading');

        icon_orig.classList.remove('is-hidden');
        icon_error.classList.add('is-hidden');
        icon_success.classList.add('is-hidden');

        var form_data = new FormData(form);
        form_data.append('ajax', 1);

        function restaure_after(self, duration)
        {
            if (self.timeoutID != undefined)
                clearTimeout(self.timeoutID)

            console.log(duration);

            self.timeoutID = setTimeout(function()
            {
                icon_orig.classList.remove('is-hidden');
                icon_error.classList.add('is-hidden');
                icon_success.classList.add('is-hidden');

                self.classList.remove('is-danger', 'is-success');
                self.classList.add('is-info');

                button_text.innerHTML = self.dataset.textOrig;
            }, duration);
        }

        ajax_json_post_call(
            '/', form_data,
            function(request, data)
            {
                self.classList.remove('is-loading', 'is-info');
                icon_orig.classList.add('is-hidden');

                var r = data.result;

                if (r == 'ok')
                {
                    icon_success.classList.remove('is-hidden');
                    self.classList.add('is-success');

                    button_text.innerHTML = self.dataset.textSucc;

                    email.value = '';
                }
                else if (r == 'ko' || r == 'ko-email')
                {
                    icon_error.classList.remove('is-hidden');
                    self.classList.add('is-danger');

                    if (r == 'ko')
                        button_text.innerHTML = self.dataset.textErrr;
                    else
                        button_text.innerHTML = self.dataset.textErrm;
                }

                restaure_after(self, r == 'ko' || r == 'ko-email' ? 6000 : 4000);
            },
            function(error, is_connection_error)
            {
                self.classList.remove('is-loading', 'is-success', 'is-danger');
                self.classList.add('is-info');
                icon_orig.classList.add('is-hidden');
                icon_error.classList.remove('is-hidden');

                button_text.innerHTML = self.dataset.textErrr;

                restaure_after(self, 6000);
            }
        );
    }
});
