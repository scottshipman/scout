import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        if (typeof this.element.showModal === 'function') {
            this.element.showModal();
        }
    }

    close() {
        this.element.close();
    }
}
