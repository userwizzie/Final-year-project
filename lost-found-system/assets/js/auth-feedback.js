(function (window, document) {
    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value == null ? '' : String(value);
        return div.innerHTML;
    }

    function findField(form, fieldRef) {
        if (!fieldRef) return null;
        if (typeof fieldRef !== 'string') return fieldRef;
        return document.getElementById(fieldRef) || form.querySelector(`[name="${fieldRef}"]`);
    }

    function getFieldBlock(field) {
        return field.closest('.mb-4, .mb-3, .mb-2, .mb-1') || field.parentElement;
    }

    function ensureFieldFeedback(field) {
        const block = getFieldBlock(field);
        if (!block) return null;

        let feedback = block.querySelector('.auth-inline-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'auth-inline-feedback';
            feedback.setAttribute('aria-live', 'polite');
            const anchor = field.closest('.field-wrap') || field;
            anchor.insertAdjacentElement('afterend', feedback);
        }

        return feedback;
    }

    function setFieldError(field, message) {
        if (!field) return;
        const feedback = ensureFieldFeedback(field);
        if (feedback) {
            feedback.textContent = message;
            feedback.classList.add('show');
        }
        field.classList.add('is-invalid');
        field.setAttribute('aria-invalid', 'true');
    }

    function clearFieldError(field) {
        if (!field) return;
        const feedback = ensureFieldFeedback(field);
        if (feedback) {
            feedback.textContent = '';
            feedback.classList.remove('show');
        }
        field.classList.remove('is-invalid');
        field.removeAttribute('aria-invalid');
    }

    function ensureSummary(form) {
        let summary = form.querySelector('.auth-form-summary');
        if (!summary) {
            summary = document.createElement('div');
            summary.className = 'auth-alert auth-form-summary alert alert-danger';
            summary.setAttribute('role', 'alert');
            summary.setAttribute('aria-live', 'assertive');
            summary.style.display = 'none';
            form.insertAdjacentElement('afterbegin', summary);
        }
        return summary;
    }

    function showSummary(form, message, type) {
        const summary = ensureSummary(form);
        const alertType = type || 'danger';
        const icon = alertType === 'success'
            ? 'check-circle'
            : alertType === 'info'
                ? 'circle-info'
                : 'triangle-exclamation';

        summary.className = `auth-alert auth-form-summary alert alert-${alertType}`;
        summary.innerHTML = `<i class="fas fa-${icon} mt-1"></i><span>${escapeHtml(message)}</span>`;
        summary.style.display = 'flex';
    }

    function clearSummary(form) {
        const summary = form.querySelector('.auth-form-summary');
        if (summary) {
            summary.style.display = 'none';
            summary.innerHTML = '';
        }
    }

    function attachValidation(config) {
        const form = typeof config.formId === 'string' ? document.getElementById(config.formId) : config.form;
        if (!form) return;

        const rules = Array.isArray(config.rules) ? config.rules : [];
        const summaryMessage = config.summaryMessage || 'Please correct the highlighted fields and try again.';
        const onValidSubmit = typeof config.onValidSubmit === 'function' ? config.onValidSubmit : null;

        const fields = [...new Set(rules.map(rule => findField(form, rule.field)).filter(Boolean))];

        function validate() {
            clearSummary(form);
            fields.forEach(clearFieldError);

            let firstError = null;

            for (const rule of rules) {
                const field = findField(form, rule.field);
                if (!field) continue;

                const isValid = rule.test(field.value, field, form);
                if (!isValid) {
                    setFieldError(field, rule.message);
                    if (!firstError) {
                        firstError = { field, message: rule.message };
                    }
                }
            }

            if (firstError) {
                showSummary(form, summaryMessage, 'danger');
                firstError.field.focus();
                return false;
            }

            return true;
        }

        form.addEventListener('submit', function (event) {
            if (!validate()) {
                event.preventDefault();
                return;
            }

            if (onValidSubmit) {
                onValidSubmit(form, event);
            }
        });

        fields.forEach(function (field) {
            ['input', 'change', 'blur'].forEach(function (eventName) {
                field.addEventListener(eventName, function () {
                    clearFieldError(field);
                    clearSummary(form);
                });
            });
        });
    }

    window.AuthFeedback = {
        attachValidation: attachValidation,
        showSummary: showSummary,
        clearSummary: clearSummary,
        setFieldError: setFieldError,
        clearFieldError: clearFieldError
    };
})(window, document);
