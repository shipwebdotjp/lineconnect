document.addEventListener('DOMContentLoaded', function () {
    const postForm = document.getElementById('post');

    postForm.addEventListener('submit', async function (event) {
        const refs = window.__rjsfFormRefs;

        if (!refs || refs.length === 0) {
            console.warn('RJSFフォーム参照が見つかりません');
            return;
        }

        let hasErrors = false;

        // すべてのフォームをバリデート
        const validations = await Promise.all(
            refs.map((ref) => {
                if (!ref?.current) return Promise.resolve(false);
                return ref.current.validateForm();
            })
        );

        // いずれかのフォームでfalse（バリデーションエラー）があれば止める
        hasErrors = validations.some(isValid => isValid === false);

        if (hasErrors) {
            event.preventDefault();
            console.warn('入力にエラーがあります。ご確認ください。');
        }
    });
});