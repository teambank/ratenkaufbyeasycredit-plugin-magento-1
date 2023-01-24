var onHydrated = function (selector, cb) {
	window.setTimeout(function() {
		var el = document.querySelector(selector);
		if (!el || !el.classList.contains('hydrated')) {
			return onHydrated(selector, cb);
		}
		cb(el);
	}, 50)
};

var appylEasyCreditCheckoutBehavior = function() {
	var validator = document.querySelector('#easycredit-submit');

	onHydrated('easycredit-checkout', function(el){
		el.addEventListener('submit', function(e){
			validator.setAttribute('value', 1);

			var duration = document.querySelector('#easycredit-number-of-installments');
			if (e.detail && 
				e.detail.numberOfInstallments && 
				duration
			) {
				duration.value = e.detail.numberOfInstallments
			}

			payment.save();
		});

		document
			.getElementById('payment-buttons-container')
			.querySelector('button')
			.addEventListener('click', function() {
				if (!document.getElementById('p_method_easycredit') ||
					!document.getElementById('p_method_easycredit').checked
				) {
					return true;
				}

				if (validator.value !== 1) {
					el.dispatchEvent(new Event('openModal'));
				}
			});
	});
};