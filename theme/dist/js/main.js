(() => {
  var __accessCheck = (obj, member, msg) => {
    if (!member.has(obj))
      throw TypeError("Cannot " + msg);
  };
  var __privateGet = (obj, member, getter) => {
    __accessCheck(obj, member, "read from private field");
    return getter ? getter.call(obj) : member.get(obj);
  };
  var __privateAdd = (obj, member, value) => {
    if (member.has(obj))
      throw TypeError("Cannot add the same private member more than once");
    member instanceof WeakSet ? member.add(obj) : member.set(obj, value);
  };
  var __privateSet = (obj, member, value, setter) => {
    __accessCheck(obj, member, "write to private field");
    setter ? setter.call(obj, value) : member.set(obj, value);
    return value;
  };
  var __privateMethod = (obj, member, method) => {
    __accessCheck(obj, member, "access private method");
    return method;
  };

  // node_modules/@joinbox/slide/slide.js
  var slide = function() {
    let { element, targetSize, dimension = "y" } = arguments.length > 0 && arguments[0] !== void 0 ? arguments[0] : {};
    if (!(element instanceof HTMLElement)) {
      throw new Error(`slide: expected parameter element to be a HTMLElement, got ${element} instead.`);
    }
    if (![
      "x",
      "y"
    ].includes(dimension)) {
      throw new Error(`slide: expected parameter dimension to be either 'x' or 'y', got ${dimension} instead.`);
    }
    const dimensionName = dimension === "x" ? "Width" : "Height";
    const initialSize = element[`offset${dimensionName}`];
    targetSize = targetSize ?? element[`scroll${dimensionName}`];
    requestAnimationFrame(() => {
      element.style[dimensionName.toLowerCase()] = `${initialSize}px`;
      requestAnimationFrame(() => {
        element.style[dimensionName.toLowerCase()] = `${targetSize}px`;
      });
    });
    const handleTransitionEnd = (param) => {
      let { target, propertyName } = param;
      if (target !== element)
        return;
      if (propertyName !== dimensionName.toLowerCase())
        return;
      element.removeEventListener("transitionend", handleTransitionEnd);
      if (element[`offset${dimensionName}`] === element[`scroll${dimensionName}`]) {
        requestAnimationFrame(() => element.style[dimensionName.toLowerCase()] = "auto");
      }
    };
    element.addEventListener("transitionend", handleTransitionEnd);
  };

  // src/js/readAttribute.js
  function readAttribute_default(element, attributeName) {
    let { transform = (value2) => value2, validate = () => true, expectation } = arguments.length > 2 && arguments[2] !== void 0 ? arguments[2] : {};
    const value = element.getAttribute(attributeName);
    const transformedValue = transform(value);
    if (!validate(transformedValue)) {
      throw new Error(`Expected attribute ${attributeName} of element ${element} to be ${expectation}; got ${transformedValue} instead (${value} before the transform function was applied).`);
    }
    return transformedValue;
  }

  // src/js/CollapsibleItem.js
  var _trigger, _detail, _isOpen, _collapsibleGroupId, _collapsibleToggleEventName, _collapsibleOpenAttributeName, _registerSummaryClickListener, registerSummaryClickListener_fn, _registerCollapsibleToggleListener, registerCollapsibleToggleListener_fn, _handleCollapsibleToggleEvent, handleCollapsibleToggleEvent_fn, _toggleDetail, toggleDetail_fn, _openDetail, openDetail_fn, _closeDetail, closeDetail_fn, _scrollIntoView, scrollIntoView_fn, _dispatchCollapsibleToggleEvent, dispatchCollapsibleToggleEvent_fn, _isInSameGroupAs, isInSameGroupAs_fn, _isItself, isItself_fn;
  var _CollapsibleItem = class extends HTMLElement {
    constructor() {
      super();
      __privateAdd(this, _registerSummaryClickListener);
      __privateAdd(this, _registerCollapsibleToggleListener);
      __privateAdd(this, _handleCollapsibleToggleEvent);
      __privateAdd(this, _toggleDetail);
      __privateAdd(this, _openDetail);
      __privateAdd(this, _closeDetail);
      __privateAdd(this, _scrollIntoView);
      __privateAdd(this, _dispatchCollapsibleToggleEvent);
      __privateAdd(this, _isInSameGroupAs);
      __privateAdd(this, _isItself);
      __privateAdd(this, _trigger, void 0);
      __privateAdd(this, _detail, void 0);
      __privateAdd(this, _isOpen, void 0);
      __privateAdd(this, _collapsibleGroupId, void 0);
    }
    connectedCallback() {
      __privateSet(this, _trigger, this.querySelector("[data-collapsible-trigger]"));
      __privateSet(this, _detail, this.querySelector("[data-collapsible-detail]"));
      __privateSet(this, _isOpen, false);
      __privateSet(this, _collapsibleGroupId, readAttribute_default(this, "data-collapsible-group-id", {
        validate: (value) => !!value,
        expectation: "a non-empty string"
      }));
      if (!(__privateGet(this, _trigger) instanceof HTMLElement)) {
        throw new Error(`CollapsibleItem: this.#trigger is expected to be an instance of HTMLElement. Got ${__privateGet(this, _trigger)} instead`);
      }
      if (!(__privateGet(this, _detail) instanceof HTMLElement)) {
        throw new Error(`CollapsibleItem: this.#detail is expected to be an instance of HTMLElement. Got ${__privateGet(this, _detail)} instead`);
      }
      __privateMethod(this, _registerSummaryClickListener, registerSummaryClickListener_fn).call(this);
      __privateMethod(this, _registerCollapsibleToggleListener, registerCollapsibleToggleListener_fn).call(this);
    }
  };
  var CollapsibleItem = _CollapsibleItem;
  _trigger = new WeakMap();
  _detail = new WeakMap();
  _isOpen = new WeakMap();
  _collapsibleGroupId = new WeakMap();
  _collapsibleToggleEventName = new WeakMap();
  _collapsibleOpenAttributeName = new WeakMap();
  _registerSummaryClickListener = new WeakSet();
  registerSummaryClickListener_fn = function() {
    __privateGet(this, _trigger).addEventListener("click", __privateMethod(this, _dispatchCollapsibleToggleEvent, dispatchCollapsibleToggleEvent_fn).bind(this));
  };
  _registerCollapsibleToggleListener = new WeakSet();
  registerCollapsibleToggleListener_fn = function() {
    window.addEventListener(__privateGet(_CollapsibleItem, _collapsibleToggleEventName), __privateMethod(this, _handleCollapsibleToggleEvent, handleCollapsibleToggleEvent_fn).bind(this));
  };
  _handleCollapsibleToggleEvent = new WeakSet();
  handleCollapsibleToggleEvent_fn = function(event) {
    if (__privateMethod(this, _isItself, isItself_fn).call(this, event.target)) {
      __privateMethod(this, _toggleDetail, toggleDetail_fn).call(this);
    } else if (__privateMethod(this, _isInSameGroupAs, isInSameGroupAs_fn).call(this, event.detail.collapsibleGroupId) && __privateGet(this, _isOpen)) {
      __privateMethod(this, _closeDetail, closeDetail_fn).call(this);
    }
  };
  _toggleDetail = new WeakSet();
  toggleDetail_fn = function() {
    if (__privateGet(this, _isOpen)) {
      __privateMethod(this, _closeDetail, closeDetail_fn).call(this);
    } else {
      __privateMethod(this, _openDetail, openDetail_fn).call(this);
    }
  };
  _openDetail = new WeakSet();
  openDetail_fn = function() {
    __privateSet(this, _isOpen, true);
    requestAnimationFrame(() => {
      this.toggleAttribute(__privateGet(_CollapsibleItem, _collapsibleOpenAttributeName), true);
    });
    slide({
      element: __privateGet(this, _detail)
    });
    __privateMethod(this, _scrollIntoView, scrollIntoView_fn).call(this);
  };
  _closeDetail = new WeakSet();
  closeDetail_fn = function() {
    __privateSet(this, _isOpen, false);
    requestAnimationFrame(() => {
      this.toggleAttribute(__privateGet(_CollapsibleItem, _collapsibleOpenAttributeName), false);
    });
    slide({
      element: __privateGet(this, _detail),
      targetSize: 0
    });
  };
  _scrollIntoView = new WeakSet();
  scrollIntoView_fn = function() {
    this.scrollIntoView({
      behavior: "smooth",
      block: "center"
    });
  };
  _dispatchCollapsibleToggleEvent = new WeakSet();
  dispatchCollapsibleToggleEvent_fn = function() {
    const payload = {
      bubbles: true,
      detail: {
        collapsibleGroupId: __privateGet(this, _collapsibleGroupId)
      }
    };
    this.dispatchEvent(new CustomEvent(__privateGet(_CollapsibleItem, _collapsibleToggleEventName), payload));
  };
  _isInSameGroupAs = new WeakSet();
  isInSameGroupAs_fn = function(collapsibleGroupId) {
    return collapsibleGroupId === __privateGet(this, _collapsibleGroupId);
  };
  _isItself = new WeakSet();
  isItself_fn = function(item) {
    return item === this;
  };
  __privateAdd(CollapsibleItem, _collapsibleToggleEventName, "collapsibleToggle");
  __privateAdd(CollapsibleItem, _collapsibleOpenAttributeName, "data-collapsible-is-open");
  if (!window.customElements.get("collapsible-item")) {
    window.customElements.define("collapsible-item", CollapsibleItem);
  }
})();
//# sourceMappingURL=main.js.map
