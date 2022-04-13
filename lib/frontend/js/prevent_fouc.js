function sv_prevent_fouc_show_content(){
	document.querySelector('body').classList.remove('sv100_companion_fouc');
	document.dispatchEvent( new Event('sv100_companion_fouc_done') );
}
window.addEventListener('load', sv_prevent_fouc_show_content);
setTimeout(sv_prevent_fouc_show_content, 5000); // fallback