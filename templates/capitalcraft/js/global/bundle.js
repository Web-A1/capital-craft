var __getOwnPropNames = Object.getOwnPropertyNames;
var __esm = (fn, res) => function __init() {
  return fn && (res = (0, fn[__getOwnPropNames(fn)[0]])(fn = 0)), res;
};
var __commonJS = (cb, mod) => function __require() {
  return mod || (0, cb[__getOwnPropNames(cb)[0]])((mod = { exports: {} }).exports, mod), mod.exports;
};

// templates/capitalcraft/js/global/burger.js
var initBurger;
var init_burger = __esm({
  "templates/capitalcraft/js/global/burger.js"() {
    "use strict";
    initBurger = () => {
      const burger = document.querySelector(".burger");
      const header = document.querySelector(".site-header");
      const mobileNav = document.querySelector(".mobile-nav");
      if (!burger || !header || !mobileNav) return;
      const closeMenu = () => {
        burger.classList.remove("active");
        burger.setAttribute("aria-expanded", "false");
        document.body.classList.remove("menu-open");
        if (window.headerControl) {
          setTimeout(() => {
            window.headerControl.unfreeze();
          }, 100);
        }
      };
      const openMenu = () => {
        burger.classList.add("active");
        burger.setAttribute("aria-expanded", "true");
        document.body.classList.add("menu-open");
        if (window.headerControl) {
          window.headerControl.freeze();
        }
      };
      burger.addEventListener("click", () => {
        const isMenuOpen = document.body.classList.contains("menu-open");
        if (isMenuOpen) {
          closeMenu();
        } else {
          openMenu();
        }
      });
      const navLinks = mobileNav.querySelectorAll("a");
      navLinks.forEach((link) => {
        link.addEventListener("click", closeMenu);
      });
    };
  }
});

// templates/capitalcraft/js/vendor/micromodal.es.min.js
function e(e2, t2) {
  for (var o2 = 0; o2 < t2.length; o2++) {
    var n2 = t2[o2];
    n2.enumerable = n2.enumerable || false, n2.configurable = true, "value" in n2 && (n2.writable = true), Object.defineProperty(e2, n2.key, n2);
  }
}
function t(e2) {
  return function(e3) {
    if (Array.isArray(e3)) return o(e3);
  }(e2) || function(e3) {
    if ("undefined" != typeof Symbol && Symbol.iterator in Object(e3)) return Array.from(e3);
  }(e2) || function(e3, t2) {
    if (!e3) return;
    if ("string" == typeof e3) return o(e3, t2);
    var n2 = Object.prototype.toString.call(e3).slice(8, -1);
    "Object" === n2 && e3.constructor && (n2 = e3.constructor.name);
    if ("Map" === n2 || "Set" === n2) return Array.from(e3);
    if ("Arguments" === n2 || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n2)) return o(e3, t2);
  }(e2) || function() {
    throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.");
  }();
}
function o(e2, t2) {
  (null == t2 || t2 > e2.length) && (t2 = e2.length);
  for (var o2 = 0, n2 = new Array(t2); o2 < t2; o2++) n2[o2] = e2[o2];
  return n2;
}
var n, i, a, r, s, l, micromodal_es_min_default;
var init_micromodal_es_min = __esm({
  "templates/capitalcraft/js/vendor/micromodal.es.min.js"() {
    l = (n = ["a[href]", "area[href]", 'input:not([disabled]):not([type="hidden"]):not([aria-hidden])', "select:not([disabled]):not([aria-hidden])", "textarea:not([disabled]):not([aria-hidden])", "button:not([disabled]):not([aria-hidden])", "iframe", "object", "embed", "[contenteditable]", '[tabindex]:not([tabindex^="-"])'], i = function() {
      function o2(e2) {
        var n2 = e2.targetModal, i3 = e2.triggers, a3 = void 0 === i3 ? [] : i3, r3 = e2.onShow, s2 = void 0 === r3 ? function() {
        } : r3, l2 = e2.onClose, c = void 0 === l2 ? function() {
        } : l2, d = e2.openTrigger, u = void 0 === d ? "data-micromodal-trigger" : d, f = e2.closeTrigger, h = void 0 === f ? "data-micromodal-close" : f, v = e2.openClass, g = void 0 === v ? "is-open" : v, m = e2.disableScroll, b = void 0 !== m && m, y = e2.disableFocus, p = void 0 !== y && y, w = e2.awaitCloseAnimation, E = void 0 !== w && w, k = e2.awaitOpenAnimation, M = void 0 !== k && k, A = e2.debugMode, C = void 0 !== A && A;
        !function(e3, t2) {
          if (!(e3 instanceof t2)) throw new TypeError("Cannot call a class as a function");
        }(this, o2), this.modal = "string" == typeof n2 ? document.getElementById(n2) : n2, this.config = { debugMode: C, disableScroll: b, openTrigger: u, closeTrigger: h, openClass: g, onShow: s2, onClose: c, awaitCloseAnimation: E, awaitOpenAnimation: M, disableFocus: p }, a3.length > 0 && this.registerTriggers.apply(this, t(a3)), this.onClick = this.onClick.bind(this), this.onKeydown = this.onKeydown.bind(this);
      }
      var i2, a2, r2;
      return i2 = o2, (a2 = [{ key: "registerTriggers", value: function() {
        for (var e2 = this, t2 = arguments.length, o3 = new Array(t2), n2 = 0; n2 < t2; n2++) o3[n2] = arguments[n2];
        o3.filter(Boolean).forEach(function(t3) {
          t3.addEventListener("click", function(t4) {
            return e2.showModal(t4);
          });
        });
      } }, { key: "showModal", value: function() {
        var e2 = this, t2 = arguments.length > 0 && void 0 !== arguments[0] ? arguments[0] : null;
        if (this.activeElement = document.activeElement, this.modal.setAttribute("aria-hidden", "false"), this.modal.classList.add(this.config.openClass), this.scrollBehaviour("disable"), this.addEventListeners(), this.config.awaitOpenAnimation) {
          var o3 = function t3() {
            e2.modal.removeEventListener("animationend", t3, false), e2.setFocusToFirstNode();
          };
          this.modal.addEventListener("animationend", o3, false);
        } else this.setFocusToFirstNode();
        this.config.onShow(this.modal, this.activeElement, t2);
      } }, { key: "closeModal", value: function() {
        var e2 = arguments.length > 0 && void 0 !== arguments[0] ? arguments[0] : null, t2 = this.modal;
        if (this.modal.setAttribute("aria-hidden", "true"), this.removeEventListeners(), this.scrollBehaviour("enable"), this.activeElement && this.activeElement.focus && this.activeElement.focus(), this.config.onClose(this.modal, this.activeElement, e2), this.config.awaitCloseAnimation) {
          var o3 = this.config.openClass;
          this.modal.addEventListener("animationend", function e3() {
            t2.classList.remove(o3), t2.removeEventListener("animationend", e3, false);
          }, false);
        } else t2.classList.remove(this.config.openClass);
      } }, { key: "closeModalByIdOrElement", value: function(e2) {
        this.modal = "string" == typeof e2 ? document.getElementById(e2) : e2, this.modal && this.closeModal();
      } }, { key: "scrollBehaviour", value: function(e2) {
        if (this.config.disableScroll) {
          var t2 = document.querySelector("body");
          switch (e2) {
            case "enable":
              Object.assign(t2.style, { overflow: "" });
              break;
            case "disable":
              Object.assign(t2.style, { overflow: "hidden" });
          }
        }
      } }, { key: "addEventListeners", value: function() {
        this.modal.addEventListener("touchstart", this.onClick), this.modal.addEventListener("click", this.onClick), document.addEventListener("keydown", this.onKeydown);
      } }, { key: "removeEventListeners", value: function() {
        this.modal.removeEventListener("touchstart", this.onClick), this.modal.removeEventListener("click", this.onClick), document.removeEventListener("keydown", this.onKeydown);
      } }, { key: "onClick", value: function(e2) {
        (e2.target.hasAttribute(this.config.closeTrigger) || e2.target.parentNode.hasAttribute(this.config.closeTrigger)) && (e2.preventDefault(), e2.stopPropagation(), this.closeModal(e2));
      } }, { key: "onKeydown", value: function(e2) {
        27 === e2.keyCode && this.closeModal(e2), 9 === e2.keyCode && this.retainFocus(e2);
      } }, { key: "getFocusableNodes", value: function() {
        var e2 = this.modal.querySelectorAll(n);
        return Array.apply(void 0, t(e2));
      } }, { key: "setFocusToFirstNode", value: function() {
        var e2 = this;
        if (!this.config.disableFocus) {
          var t2 = this.getFocusableNodes();
          if (0 !== t2.length) {
            var o3 = t2.filter(function(t3) {
              return !t3.hasAttribute(e2.config.closeTrigger);
            });
            o3.length > 0 && o3[0].focus(), 0 === o3.length && t2[0].focus();
          }
        }
      } }, { key: "retainFocus", value: function(e2) {
        var t2 = this.getFocusableNodes();
        if (0 !== t2.length) if (t2 = t2.filter(function(e3) {
          return null !== e3.offsetParent;
        }), this.modal.contains(document.activeElement)) {
          var o3 = t2.indexOf(document.activeElement);
          e2.shiftKey && 0 === o3 && (t2[t2.length - 1].focus(), e2.preventDefault()), !e2.shiftKey && t2.length > 0 && o3 === t2.length - 1 && (t2[0].focus(), e2.preventDefault());
        } else t2[0].focus();
      } }]) && e(i2.prototype, a2), r2 && e(i2, r2), o2;
    }(), a = null, r = function(e2) {
      if ("string" == typeof id ? !document.getElementById(e2) : !e2) return console.warn("MicroModal: \u2757Seems like you have missed %c'".concat(e2, "'"), "background-color: #f8f9fa;color: #50596c;font-weight: bold;", "ID somewhere in your code. Refer example below to resolve it."), console.warn("%cExample:", "background-color: #f8f9fa;color: #50596c;font-weight: bold;", '<div class="modal" id="'.concat(e2, '"></div>')), false;
    }, s = function(e2, t2) {
      if (function(e3) {
        e3.length <= 0 && (console.warn("MicroModal: \u2757Please specify at least one %c'micromodal-trigger'", "background-color: #f8f9fa;color: #50596c;font-weight: bold;", "data attribute."), console.warn("%cExample:", "background-color: #f8f9fa;color: #50596c;font-weight: bold;", '<a href="#" data-micromodal-trigger="my-modal"></a>'));
      }(e2), !t2) return true;
      for (var o2 in t2) r(o2);
      return true;
    }, { init: function(e2) {
      var o2 = Object.assign({}, { openTrigger: "data-micromodal-trigger" }, e2), n2 = t(document.querySelectorAll("[".concat(o2.openTrigger, "]"))), r2 = function(e3, t2) {
        var o3 = [];
        return e3.forEach(function(e4) {
          var n3 = e4.attributes[t2].value;
          void 0 === o3[n3] && (o3[n3] = []), o3[n3].push(e4);
        }), o3;
      }(n2, o2.openTrigger);
      if (true !== o2.debugMode || false !== s(n2, r2)) for (var l2 in r2) {
        var c = r2[l2];
        o2.targetModal = l2, o2.triggers = t(c), a = new i(o2);
      }
    }, show: function(e2, t2) {
      var o2 = t2 || {};
      o2.targetModal = e2, true === o2.debugMode && false === r(e2) || (a && a.removeEventListeners(), (a = new i(o2)).showModal());
    }, close: function(e2) {
      e2 ? a.closeModalByIdOrElement(e2) : a.closeModal();
    } });
    "undefined" != typeof window && (window.MicroModal = l);
    micromodal_es_min_default = l;
  }
});

// templates/capitalcraft/js/global/modal.js
var initModal;
var init_modal = __esm({
  "templates/capitalcraft/js/global/modal.js"() {
    "use strict";
    init_micromodal_es_min();
    initModal = () => {
      micromodal_es_min_default.init({
        disableScroll: true,
        disableFocus: true,
        onShow: () => {
          const form = document.getElementById("contactForm");
          if (form) {
            form.style.display = "flex";
            form.reset();
            const err = form.querySelector(".form-error");
            if (err) err.style.display = "none";
            const consentErr = form.querySelector(".consent-error");
            if (consentErr) consentErr.style.display = "none";
            const errorResult = form.querySelector(".form-result.error");
            if (errorResult) errorResult.style.display = "none";
          }
          const header = document.querySelector("#contact-modal .modal__header");
          if (header) header.style.display = "flex";
          const successBox = document.querySelector(
            "#contact-modal .modal__success"
          );
          if (successBox) successBox.style.display = "none";
          document.body.classList.add("modal-open");
        },
        onClose: () => {
          document.body.classList.remove("modal-open");
          if (window.headerControl) {
            window.headerControl.pin();
          }
        }
      });
    };
  }
});

// templates/capitalcraft/js/global/phone-mask.js
var initPhoneMask;
var init_phone_mask = __esm({
  "templates/capitalcraft/js/global/phone-mask.js"() {
    "use strict";
    initPhoneMask = () => {
      const form = document.getElementById("contactForm");
      const phoneInput = form ? form.querySelector('input[name="phone"]') : null;
      if (!phoneInput) return;
      phoneInput.addEventListener("input", function() {
        let digits = this.value.replace(/\D/g, "").replace(/^8/, "7");
        if (digits.charAt(0) !== "7") {
          digits = "7" + digits;
        }
        if (digits.length > 11) {
          digits = digits.slice(0, 11);
        }
        let formatted = "+7";
        if (digits.length > 1) {
          formatted += " (" + digits.slice(1, 4);
          if (digits.length >= 4) formatted += ") ";
        }
        if (digits.length >= 4) {
          formatted += digits.slice(4, 7);
        }
        if (digits.length >= 7) {
          formatted += "-" + digits.slice(7, 9);
        }
        if (digits.length >= 9) {
          formatted += "-" + digits.slice(9, 11);
        }
        this.value = formatted;
        const errEl = form ? form.querySelector(".form-error") : null;
        if (errEl) errEl.style.display = "none";
      });
    };
  }
});

// templates/capitalcraft/js/global/form-submit.js
var initFormSubmit;
var init_form_submit = __esm({
  "templates/capitalcraft/js/global/form-submit.js"() {
    "use strict";
    initFormSubmit = () => {
      const form = document.getElementById("contactForm");
      if (!form) return;
      const phoneInput = form.querySelector('input[name="phone"]');
      const consentInput = form.querySelector(".personal-data input");
      const consentError = form.querySelector(".consent-error");
      const header = document.querySelector("#contact-modal .modal__header");
      const successBox = document.querySelector("#contact-modal .modal__success");
      if (consentInput) {
        consentInput.addEventListener("change", () => {
          if (consentError) consentError.style.display = "none";
        });
      }
      form.addEventListener("submit", async (e2) => {
        e2.preventDefault();
        const phone = phoneInput ? phoneInput.value.replace(/\D/g, "") : "";
        const error = form.querySelector(".form-error");
        let message = "";
        if (!phone) {
          message = "\u041F\u043E\u0436\u0430\u043B\u0443\u0439\u0441\u0442\u0430, \u0432\u0432\u0435\u0434\u0438\u0442\u0435 \u043D\u043E\u043C\u0435\u0440 \u0442\u0435\u043B\u0435\u0444\u043E\u043D\u0430";
        } else if (phone.length !== 11 || !phone.startsWith("7")) {
          message = "\u0412\u0432\u0435\u0434\u0438\u0442\u0435 \u043A\u043E\u0440\u0440\u0435\u043A\u0442\u043D\u044B\u0439 \u043D\u043E\u043C\u0435\u0440 \u0442\u0435\u043B\u0435\u0444\u043E\u043D\u0430";
        }
        const valid = !message;
        if (message) {
          error.textContent = message;
          error.style.display = "block";
        } else {
          error.style.display = "none";
        }
        const consentValid = !consentInput || consentInput.checked;
        if (!consentValid && consentError) {
          consentError.style.display = "block";
        } else if (consentError) {
          consentError.style.display = "none";
        }
        if (!valid || !consentValid) return;
        const fd = new FormData(form);
        try {
          const response = await fetch(
            "templates/capitalcraft/partials/_send_to_telegram.php",
            {
              method: "POST",
              body: fd
            }
          );
          if (!response.ok) {
            throw new Error(`Network error: ${response.status}`);
          }
          const data = await response.json();
          if (data.status === "ok") {
            const errorRes = form.querySelector(".form-result.error");
            if (errorRes) errorRes.style.display = "none";
            form.style.display = "none";
            if (header) header.style.display = "none";
            if (successBox) successBox.style.display = "flex";
          } else {
            throw new Error();
          }
        } catch (err) {
          form.querySelector(".form-result.error").style.display = "block";
          if (successBox) successBox.style.display = "none";
        }
      });
    };
  }
});

// templates/capitalcraft/js/global/scroll-top.js
var initScrollTop;
var init_scroll_top = __esm({
  "templates/capitalcraft/js/global/scroll-top.js"() {
    "use strict";
    initScrollTop = () => {
      const btn = document.querySelector(".scroll-top");
      if (!btn) return;
      const toggleVisibility = () => {
        if (window.scrollY > 300) {
          btn.classList.add("visible");
        } else {
          btn.classList.remove("visible");
        }
      };
      btn.addEventListener("click", (e2) => {
        e2.preventDefault();
        window.scrollTo({ top: 0, behavior: "smooth" });
      });
      window.addEventListener("scroll", toggleVisibility);
      toggleVisibility();
    };
  }
});

// templates/capitalcraft/js/global/script.js
var require_script = __commonJS({
  "templates/capitalcraft/js/global/script.js"() {
    init_burger();
    init_modal();
    init_phone_mask();
    init_form_submit();
    init_scroll_top();
    initBurger();
    initModal();
    initPhoneMask();
    initFormSubmit();
    initScrollTop();
    var header = document.querySelector(".site-header");
    if (header) {
      let lastScrollY = window.pageYOffset;
      let frozen = false;
      const tolerance = window.innerWidth <= 767 ? { up: 3, down: 5 } : { up: 5, down: 10 };
      const onScroll = () => {
        if (frozen) return;
        const currentScrollY = window.pageYOffset;
        if (currentScrollY > lastScrollY && currentScrollY - lastScrollY > tolerance.down) {
          header.classList.remove("pinned");
          header.classList.add("unpinned");
        } else if (currentScrollY < lastScrollY && lastScrollY - currentScrollY > tolerance.up) {
          header.classList.remove("unpinned");
          header.classList.add("pinned");
        }
        lastScrollY = currentScrollY;
      };
      window.addEventListener("scroll", onScroll);
      window.headerControl = {
        freeze() {
          frozen = true;
        },
        unfreeze() {
          frozen = false;
        },
        pin() {
          header.classList.remove("unpinned");
          header.classList.add("pinned");
        }
      };
    }
  }
});
export default require_script();
