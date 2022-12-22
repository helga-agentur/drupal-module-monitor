import slide from '@joinbox/slide';
import readAttribute from './readAttribute.js';

/* global window, HTMLElement, CustomEvent, requestAnimationFrame */

/**
 * An element with a summary and a detail section where the detail section is collapsible.
 * If an item in a group is opened
 * - it scrolls to the center of the screen
 * - all other items of the same group are closed
 *
 * @attr data-collapsible-is-open  boolen attribute which indicates if the detail section is open
 * @attr data-collapsible-summary  marks the summary section
 * @attr data-collapsible-detail   marks the detail section
 */
class CollapsibleItem extends HTMLElement {

    #trigger;
    #detail;
    #isOpen;
    #collapsibleGroupId;

    static #collapsibleToggleEventName = 'collapsibleToggle';
    static #collapsibleOpenAttributeName = 'data-collapsible-is-open';

    constructor() {
        super();
    }

    connectedCallback() {
        this.#trigger = this.querySelector('[data-collapsible-trigger]');
        this.#detail = this.querySelector('[data-collapsible-detail]');
        this.#isOpen = false;
        this.#collapsibleGroupId = readAttribute(
            this,
            'data-collapsible-group-id',
            {
                validate: (value) => !!value,
                expectation: 'a non-empty string',
            },
        );

        if (!(this.#trigger instanceof HTMLElement)) {
            throw new Error(`CollapsibleItem: this.#trigger is expected to be an instance of HTMLElement. Got ${this.#trigger} instead`);
        }

        if (!(this.#detail instanceof HTMLElement)) {
            throw new Error(`CollapsibleItem: this.#detail is expected to be an instance of HTMLElement. Got ${this.#detail} instead`);
        }
        this.#registerSummaryClickListener();
        this.#registerCollapsibleToggleListener();
    }

    #registerSummaryClickListener() {
        this.#trigger.addEventListener('click', this.#dispatchCollapsibleToggleEvent.bind(this));
    }

    #registerCollapsibleToggleListener() {
        window.addEventListener(
            CollapsibleItem.#collapsibleToggleEventName,
            this.#handleCollapsibleToggleEvent.bind(this),
        );
    }

    #handleCollapsibleToggleEvent(event) {
        if (this.#isItself(event.target)) {
            this.#toggleDetail();
        } else if (this.#isInSameGroupAs(event.detail.collapsibleGroupId) && this.#isOpen) {
            this.#closeDetail();
        }
    }

    #toggleDetail() {
        if (this.#isOpen) {
            this.#closeDetail();
        } else {
            this.#openDetail();
        }
    }

    #openDetail() {
        this.#isOpen = true;
        requestAnimationFrame(() => {
            this.toggleAttribute(CollapsibleItem.#collapsibleOpenAttributeName, true);
        });
        slide({ element: this.#detail });
        this.#scrollIntoView();
    }

    #closeDetail() {
        this.#isOpen = false;
        requestAnimationFrame(() => {
            this.toggleAttribute(CollapsibleItem.#collapsibleOpenAttributeName, false);
        });
        slide({ element: this.#detail, targetSize: 0 });
    }

    #scrollIntoView() {
        this.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    #dispatchCollapsibleToggleEvent() {
        const payload = {
            bubbles: true,
            detail: {
                collapsibleGroupId: this.#collapsibleGroupId,
            },
        };

        this.dispatchEvent(new CustomEvent(CollapsibleItem.#collapsibleToggleEventName, payload));
    }

    #isInSameGroupAs(collapsibleGroupId) {
        return collapsibleGroupId === this.#collapsibleGroupId;
    }

    #isItself(item) {
        return item === this;
    }

}


if (!window.customElements.get('collapsible-item')) {
    window.customElements.define('collapsible-item', CollapsibleItem);
}
