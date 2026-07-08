import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['dialog'];

    connect() {
        this.onBackdropClick = (event) => {
            if (event.target === this.dialogTarget) {
                this.close();
            }
        };
        this.onSubmitEnd = (event) => {
            if (this.dialogTarget.contains(event.target) && event.detail.success) {
                this.close();
            }
        };

        this.dialogTarget.addEventListener('click', this.onBackdropClick);
        document.addEventListener('turbo:submit-end', this.onSubmitEnd);
    }

    disconnect() {
        this.dialogTarget.removeEventListener('click', this.onBackdropClick);
        document.removeEventListener('turbo:submit-end', this.onSubmitEnd);
    }

    open() {
        this.dialogTarget.showModal();
    }

    close() {
        this.dialogTarget.close();
    }
}
