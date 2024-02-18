function lmCopyText(input_id) {
    // eslint-disable-next-line no-console
    console.log(input_id);
    const el = document.getElementById(input_id);
    el.select(); 
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(el.value).then(() => {
            console.log(el.value);
        }).catch(err => {
            console.error('Failed to copy text: ', err);
        });
    } else {
        console.log('Clipboard API not available');
    }
}
