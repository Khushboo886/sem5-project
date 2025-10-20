document.addEventListener('DOMContentLoaded', function(){
	const btn = document.getElementById('cc-hamburger');
	const sidebar = document.getElementById('cc-sidebar');

	if (!btn || !sidebar) return;

	function setOpen(open){
		btn.classList.toggle('active', open);
		btn.setAttribute('aria-expanded', open ? 'true' : 'false');
		sidebar.setAttribute('aria-hidden', open ? 'false' : 'true');
	}

	btn.addEventListener('click', function(e){
		const isOpen = btn.classList.contains('active');
		setOpen(!isOpen);
	});

	// close when clicking outside on small screens
	document.addEventListener('click', function(e){
		if (!sidebar.contains(e.target) && !btn.contains(e.target)){
			setOpen(false);
		}
	});

	// close on Escape
	document.addEventListener('keydown', function(e){
		if (e.key === 'Escape') setOpen(false);
	});
});

