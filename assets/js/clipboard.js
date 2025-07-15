// const { __ } = wp.i18n;

function copyToClipboard(text, element) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text)
            .then(() => {
                // Show feedback
                const originalText = element.textContent;
                element.textContent = '✓ ' + wp.i18n.__('Copied!', 'lineconnect');

                // Reset after 2 seconds
                setTimeout(() => {
                    element.textContent = originalText;
                }, 2000);
            })
            .catch(err => {
                console.error('Failed to copy: ', err);
                // Fallback
                fallbackCopyTextToClipboard(text, element);
            });
    } else {
        // Fallback for browsers that don't support clipboard API
        fallbackCopyTextToClipboard(text, element);
    }
}

function fallbackCopyTextToClipboard(text, element) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.position = "fixed";  // Avoid scrolling to bottom
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();

    try {
        const successful = document.execCommand('copy');
        if (successful) {
            // Show feedback
            const originalText = element.textContent;
            element.textContent = '✓ ' + wp.i18n.__('Copied!', 'lineconnect');

            // Reset after 2 seconds
            setTimeout(() => {
                element.textContent = originalText;
            }, 2000);
        }
    } catch (err) {
        console.error('Fallback: Oops, unable to copy', err);
    }

    document.body.removeChild(textArea);
}