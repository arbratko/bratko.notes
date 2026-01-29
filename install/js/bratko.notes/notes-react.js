/**
 * Модуль заметок (bratko.notes) — React-приложение (вход, регистрация, CRUD заметок).
 *
 * @author Артём Братко
 * @link   https://arbratko.ru/
 */
(function (global) {
    "use strict";

    console.log("[bratko.notes] 1. Скрипт загружен");

    var React = global.React;
    var ReactDOM = global.ReactDOM;
    if (!React || !ReactDOM) {
        console.error("[bratko.notes] React или ReactDOM не найдены. Скрипты CDN должны загружаться до notes-react.js");
        var el = document.getElementById("bratko-notes-app");
        if (el) el.innerHTML = "<div style=\"padding:1.5rem;font-family:system-ui;color:#2c2825;\">Не загружены React или ReactDOM.</div>";
        return;
    }
    console.log("[bratko.notes] 2. React и ReactDOM найдены");

    var h = React.createElement;

    function getInit() {
        return global.BRATKO_NOTES_INIT || {
            isAuthorized: false,
            userName: "",
            logoutUrl: "",
            messages: {}
        };
    }

    function msg(key, fallback) {
        var m = getInit().messages;
        return (m && m[key]) ? m[key] : (fallback || key);
    }

    function runAction(action, data) {
        if (!global.BX || !global.BX.ajax || !global.BX.ajax.runAction) {
            return Promise.reject({ errors: [{ message: "BX.ajax недоступен" }] });
        }
        return global.BX.ajax.runAction(action, data ? { data: data } : {});
    }

    function getErrorMessage(err) {
        if (err && err.errors && err.errors.length) {
            return err.errors.map(function (e) { return e.message || String(e); }).join(", ");
        }
        if (err && err.data && Array.isArray(err.data.errors)) {
            return err.data.errors.join(", ");
        }
        return "Не удалось выполнить запрос.";
    }

    function getResultData(result) {
        return (result && result.data !== undefined) ? result.data : result;
    }

    // --- Страница авторизации: левый блок + правый блок (логин + кнопка регистрации)
    function AuthPage(props) {
        var m = props.messages;
        return h("div", { className: "auth-page" },
            h("div", { className: "auth" },
                h("div", { className: "auth__left" },
                    h("h1", { className: "auth__title" }, m.authTitle),
                    h("p", { className: "auth__subtitle" }, m.authSubtitle),
                    h("div", { className: "auth__infographic", "aria-hidden": "true" },
                        h("div", { className: "auth__infographic-sheet auth__infographic-sheet--1" }),
                        h("div", { className: "auth__infographic-sheet auth__infographic-sheet--2" }),
                        h("div", { className: "auth__infographic-sheet auth__infographic-sheet--3" }),
                        h("div", { className: "auth__infographic-lines" })
                    )
                ),
                h("div", { className: "auth__right" },
                    h("p", { className: "auth__hint" }, m.authHint),
                    h(LoginForm, {
                        onSuccess: props.onLoginSuccess,
                        onClearError: props.onClearLoginError,
                        loginError: props.loginError,
                        placeholderLogin: m.placeholderLogin,
                        placeholderPassword: m.placeholderPassword,
                        btnSubmit: m.authBtnSubmit
                    }),
                    h("p", { className: "auth__register-hint" }, m.authRegisterHint),
                    h("button", {
                        type: "button",
                        className: "auth__register-btn",
                        id: "registerBtn",
                        onClick: props.onOpenRegister
                    }, m.authRegisterBtn)
                )
            )
        );
    }

    function LoginForm(props) {
        var loginRef = React.useRef(null);
        var passwordRef = React.useRef(null);
        var submittingRef = React.useRef(false);

        function onSubmit(e) {
            e.preventDefault();
            if (submittingRef.current) return;
            if (props.onClearError) props.onClearError();
            var login = (loginRef.current && loginRef.current.value) ? loginRef.current.value.trim() : "";
            var password = passwordRef.current ? passwordRef.current.value : "";
            if (!login || !password) {
                props.onSuccess({ success: false, errors: [props.placeholderLogin + " / " + props.placeholderPassword] });
                return;
            }
            submittingRef.current = true;
            runAction("bratko:notes.auth.login", { login: login, password: password })
                .then(function (result) {
                    submittingRef.current = false;
                    var data = getResultData(result);
                    if (data && data.success === true) {
                        global.location.reload();
                    } else {
                        props.onSuccess({ success: false, errors: (data && data.errors) ? data.errors : ["Ошибка входа"] });
                    }
                })
                .catch(function (err) {
                    submittingRef.current = false;
                    props.onSuccess({ success: false, errors: [getErrorMessage(err)] });
                });
        }

        return h("form", {
            className: "auth__form",
            onSubmit: onSubmit,
            noValidate: true
        },
            props.loginError ? h("p", { className: "auth__error auth__error--visible", role: "alert" }, props.loginError) : null,
            h("input", {
                type: "text",
                className: "auth__input",
                ref: loginRef,
                placeholder: props.placeholderLogin,
                "autoComplete": "username",
                required: true
            }),
            h("input", {
                type: "password",
                className: "auth__input",
                ref: passwordRef,
                placeholder: props.placeholderPassword,
                "autoComplete": "current-password",
                required: true
            }),
            h("button", { type: "submit", className: "auth__submit" }, props.btnSubmit)
        );
    }

    // --- Регистрация в модальном окне --- */
    function RegisterModal(props) {
        var open = props.open;
        if (!open) {
            return h("div", {
                className: "auth-modal",
                id: "registerModal",
                role: "dialog",
                "aria-modal": "true",
                "aria-hidden": "true",
                style: { display: "none" }
            });
        }
        var content = props.registerSuccess
            ? h("div", { className: "auth-modal__success-block" },
                h("div", { className: "auth-modal__success" },
                    h("div", { className: "auth-modal__success-title" }, msg("NOTES_REGISTER_SUCCESS_TITLE")),
                    h("div", { className: "auth-modal__timer-line" }, msg("NOTES_REGISTER_TIMER_LINE")),
                    h("div", { className: "auth-modal__timer-value", "data-seconds": "3" }, props.timerSeconds)
                )
            )
            : h("div", { className: "auth-modal__form-block" },
                props.registerError ? h("p", { className: "auth-modal__error", "aria-hidden": "false", role: "alert" }, props.registerError) : null,
                h(RegisterForm, {
                    onSuccess: props.onRegisterSuccess,
                    onCancel: props.onCloseRegister,
                    messages: props.messages
                })
            );
        return h("div", {
            className: "auth-modal",
            id: "registerModal",
            role: "dialog",
            "aria-modal": "true",
            "aria-hidden": "false"
        },
            h("div", { className: "auth-modal__backdrop", onClick: props.onCloseRegister }),
            h("div", { className: "auth-modal__box" },
                props.registerSuccess ? null : h("div", { className: "auth-modal__header" },
                    h("h2", { className: "auth-modal__title" }, msg("NOTES_LIST_MODAL_REGISTER_TITLE")),
                    h("button", { type: "button", className: "auth-modal__close", "aria-label": msg("NOTES_LIST_MODAL_CLOSE_LABEL"), onClick: props.onCloseRegister }, "×")
                ),
                content
            )
        );
    }

    function RegisterForm(props) {
        var nameRef = React.useRef(null);
        var loginRef = React.useRef(null);
        var emailRef = React.useRef(null);
        var passwordRef = React.useRef(null);
        var confirmRef = React.useRef(null);
        var submittingRef = React.useRef(false);
        var m = props.messages;

        function onSubmit(e) {
            e.preventDefault();
            if (submittingRef.current) return;
            var name = (nameRef.current && nameRef.current.value) ? nameRef.current.value.trim() : "";
            var login = (loginRef.current && loginRef.current.value) ? loginRef.current.value.trim() : "";
            var email = (emailRef.current && emailRef.current.value) ? emailRef.current.value.trim() : "";
            var password = passwordRef.current ? passwordRef.current.value : "";
            var confirm = confirmRef.current ? confirmRef.current.value : "";
            submittingRef.current = true;
            runAction("bratko:notes.register.register", { name: name, login: login, email: email, password: password, confirm: confirm })
                .then(function (result) {
                    submittingRef.current = false;
                    var data = getResultData(result);
                    if (data && data.success === true) {
                        props.onSuccess({ success: true });
                    } else {
                        props.onSuccess({ success: false, errors: (data && data.errors) ? data.errors : ["Не удалось зарегистрироваться"] });
                    }
                })
                .catch(function (err) {
                    submittingRef.current = false;
                    props.onSuccess({ success: false, errors: [getErrorMessage(err)] });
                });
        }

        return h("form", { className: "auth-modal__form", onSubmit: onSubmit, noValidate: true },
            h("input", { type: "text", className: "auth-modal__input", ref: nameRef, placeholder: m.placeholderName, "autoComplete": "name", required: true }),
            h("input", { type: "text", className: "auth-modal__input", ref: loginRef, placeholder: m.placeholderLogin, "autoComplete": "username", required: true }),
            h("input", { type: "email", className: "auth-modal__input", ref: emailRef, placeholder: m.placeholderEmail, "autoComplete": "email", required: true }),
            h("input", { type: "password", className: "auth-modal__input", ref: passwordRef, placeholder: m.placeholderPassword, "autoComplete": "new-password", required: true }),
            h("input", { type: "password", className: "auth-modal__input", ref: confirmRef, placeholder: m.placeholderPasswordConfirm, "autoComplete": "new-password", required: true }),
            h("div", { className: "auth-modal__buttons" },
                h("button", { type: "button", className: "auth-modal__btn auth-modal__btn--cancel", onClick: props.onCancel }, m.btnCancel),
                h("button", { type: "submit", className: "auth-modal__btn auth-modal__btn--submit" }, m.btnSubmit)
            )
        );
    }

    // --- Страница заметок
    function NotesPage(props) {
        var m = props.messages;
        var notes = props.notes || [];
        var error = props.error;
        var loading = props.loading;

        return h("div", { className: "app" },
            h("header", { className: "header" },
                h("div", { className: "header__main" },
                    h("h1", { className: "header__title" }, m.headerTitle),
                    h("p", { className: "header__subtitle" }, m.headerSubtitle)
                ),
                h("div", { className: "header__user" },
                    h("span", { className: "header__user-name" }, props.userName),
                    h("a", { href: props.logoutUrl, className: "header__logout" }, m.headerLogout)
                )
            ),
            h("section", { className: "add-note" },
                h(AddNoteForm, {
                    onSubmit: props.onAddNote,
                    placeholderTitle: m.placeholderTitle,
                    placeholderBody: m.placeholderBody,
                    addBtn: m.addBtn,
                    addNoteLabel: m.addNoteLabel
                })
            ),
            h("main", { className: "notes" + (notes.length > 0 ? " has-notes" : ""), id: "notesContainer" },
                error ? h("div", { className: "notes__error", "aria-live": "polite" }, error) : null,
                notes.length === 0 && !loading ? h("div", { className: "notes__empty", "aria-hidden": "false" },
                    h("div", { className: "notes__empty-icon", "aria-hidden": "true" }),
                    h("p", { className: "notes__empty-text" }, m.emptyText),
                    h("p", { className: "notes__empty-hint" }, m.emptyHint)
                ) : null,
                h("ul", { className: "notes__list", "aria-label": m.listLabel },
                    notes.map(function (note) {
                        return h(NoteItem, {
                            key: note.id,
                            note: note,
                            onEdit: props.onEditNote,
                            onDelete: props.onDeleteNote,
                            editLabel: m.btnEditLabel,
                            deleteLabel: m.btnDeleteLabel
                        });
                    })
                )
            ),
            h("footer", { className: "footer" },
                h("span", { className: "footer__count", id: "notesCount" }, notesCountText(notes.length, m.footerCount))
            ),
            props.editModalOpen ? h(EditModal, {
                note: props.editingNote,
                onSave: props.onSaveNote,
                onClose: props.onCloseEditModal,
                messages: m
            }) : null
        );
    }

    function AddNoteForm(props) {
        var titleRef = React.useRef(null);
        var bodyRef = React.useRef(null);
        function onSubmit(e) {
            e.preventDefault();
            var t = titleRef.current ? titleRef.current.value.trim() : "";
            var b = bodyRef.current ? bodyRef.current.value.trim() : "";
            if (t || b) {
                props.onSubmit(t, b);
                if (titleRef.current) titleRef.current.value = "";
                if (bodyRef.current) bodyRef.current.value = "";
            }
        }
        return h("form", { className: "add-note__form", onSubmit: onSubmit, noValidate: true },
            h("div", { className: "add-note__fields" },
                h("input", { type: "text", className: "add-note__input add-note__input--title", ref: titleRef, placeholder: props.placeholderTitle, maxLength: 80, autoComplete: "off" }),
                h("textarea", { className: "add-note__input add-note__input--body", ref: bodyRef, placeholder: props.placeholderBody, rows: 2, maxLength: 2000 })
            ),
            h("button", { type: "submit", className: "add-note__submit", "aria-label": props.addNoteLabel },
                h("span", { className: "add-note__submit-text" }, props.addBtn),
                h("svg", { className: "add-note__submit-icon", width: 20, height: 20, viewBox: "0 0 24 24", fill: "none", stroke: "currentColor", strokeWidth: 2 },
                    h("line", { x1: 12, y1: 5, x2: 12, y2: 19 }),
                    h("line", { x1: 5, y1: 12, x2: 19, y2: 12 })
                )
            )
        );
    }

    function NoteItem(props) {
        var n = props.note;
        return h("li", { className: "note", "data-id": n.id },
            h("div", { className: "note__content" },
                h("h3", { className: "note__title" }, n.title || ""),
                h("p", { className: "note__body" }, n.body || "")
            ),
            h("div", { className: "note__actions" },
                h("button", { type: "button", className: "note__btn note__btn--edit", "aria-label": props.editLabel, onClick: function () { props.onEdit(n); } }),
                h("button", { type: "button", className: "note__btn note__btn--delete", "aria-label": props.deleteLabel, onClick: function () { props.onDelete(n.id); } })
            )
        );
    }

    function notesCountText(n, fallback) {
        if (n === 0) return fallback || "0 заметок";
        var w = n === 1 ? "заметка" : (n >= 2 && n <= 4 ? "заметки" : "заметок");
        return n + " " + w;
    }

    function EditModal(props) {
        var note = props.note;
        var titleRef = React.useRef(null);
        var bodyRef = React.useRef(null);
        React.useEffect(function () {
            if (titleRef.current) titleRef.current.value = note ? note.title : "";
            if (bodyRef.current) bodyRef.current.value = note ? note.body : "";
            if (titleRef.current) setTimeout(function () { titleRef.current.focus(); }, 0);
        }, [note ? note.id : null]);
        function onSubmit(e) {
            e.preventDefault();
            var title = titleRef.current ? titleRef.current.value.trim() : "";
            var body = bodyRef.current ? bodyRef.current.value.trim() : "";
            if (note && note.id) props.onSave(note.id, title, body);
        }
        return h("div", { className: "modal", role: "dialog", "aria-modal": "true", "aria-hidden": "false" },
            h("div", { className: "modal__backdrop", onClick: props.onClose }),
            h("div", { className: "modal__box" },
                h("h2", { className: "modal__title" }, props.messages.editModalTitle),
                h("form", { className: "modal__form", onSubmit: onSubmit },
                    h("input", { type: "hidden", value: note ? note.id : "" }),
                    h("input", { type: "text", className: "modal__input", ref: titleRef, defaultValue: note ? note.title : "", placeholder: props.messages.placeholderTitle, maxLength: 80, required: true }),
                    h("textarea", { className: "modal__input modal__input--textarea", ref: bodyRef, defaultValue: note ? note.body : "", placeholder: props.messages.editPlaceholderBody, rows: 4, maxLength: 2000 }),
                    h("div", { className: "modal__buttons" },
                        h("button", { type: "button", className: "modal__btn modal__btn--cancel", onClick: props.onClose }, props.messages.modalCancel),
                        h("button", { type: "submit", className: "modal__btn modal__btn--save" }, props.messages.modalSave)
                    )
                )
            )
        );
    }

    // --- Подпись в футере
    function DeveloperFooter() {
        return h("footer", { className: "bratko-notes-dev-footer", "aria-label": "Разработчик" },
            h("a", {
                href: "https://arbratko.ru/",
                target: "_blank",
                rel: "noopener noreferrer",
                className: "bratko-notes-dev-footer__link"
            }, "Разработчик: Артём Братко")
        );
    }

    // --- App
    function App() {
        var init = getInit();
        var isAuthorized = init.isAuthorized === true || init.isAuthorized === "Y";
        var messages = init.messages || {};

        var loginState = React.useState(null);
        var loginError = loginState[0];
        var setLoginError = loginState[1];
        var registerState = React.useState(false);
        var registerOpen = registerState[0];
        var setRegisterOpen = registerState[1];
        var registerErrState = React.useState(null);
        var registerError = registerErrState[0];
        var setRegisterError = registerErrState[1];
        var registerSuccessState = React.useState(false);
        var registerSuccess = registerSuccessState[0];
        var setRegisterSuccess = registerSuccessState[1];
        var timerState = React.useState(3);
        var timerSeconds = timerState[0];
        var setTimerSeconds = timerState[1];

        var notesState = React.useState([]);
        var notes = notesState[0];
        var setNotes = notesState[1];
        var notesErrState = React.useState("");
        var notesError = notesErrState[0];
        var setNotesError = notesErrState[1];
        var notesLoadState = React.useState(false);
        var notesLoading = notesLoadState[0];
        var setNotesLoading = notesLoadState[1];
        var editModalState = React.useState(false);
        var editModalOpen = editModalState[0];
        var setEditModalOpen = editModalState[1];
        var editingNoteState = React.useState(null);
        var editingNote = editingNoteState[0];
        var setEditingNote = editingNoteState[1];

        function handleLoginSuccess(result) {
            if (result.success) return;
            setLoginError(result.errors && result.errors.length ? result.errors.join("\n") : "Ошибка входа");
        }

        function handleRegisterSuccess(result) {
            if (result.success) {
                setRegisterError(null);
                setRegisterSuccess(true);
                var s = 3;
                setTimerSeconds(s);
                var iv = setInterval(function () {
                    s--;
                    setTimerSeconds(s);
                    if (s <= 0) {
                        clearInterval(iv);
                        global.location.reload();
                    }
                }, 1000);
            } else {
                setRegisterError(result.errors && result.errors.length ? result.errors.join("\n") : "Не удалось зарегистрироваться");
            }
        }

        function loadNotes() {
            setNotesError("");
            setNotesLoading(true);
            runAction("bratko:notes.notes.list")
                .then(function (response) {
                    setNotesLoading(false);
                    var data = getResultData(response);
                    var items = (data && data.items) ? data.items : [];
                    setNotes(items.map(function (item) {
                        return { id: String(item.ID), title: item.TITLE || "", body: item.CONTENT || "" };
                    }));
                })
                .catch(function (err) {
                    setNotesLoading(false);
                    setNotesError(getErrorMessage(err));
                });
        }

        React.useEffect(function () {
            if (isAuthorized) loadNotes();
        }, [isAuthorized]);

        function handleAddNote(title, body) {
            setNotesError("");
            runAction("bratko:notes.notes.add", { title: title, content: body })
                .then(function () { loadNotes(); })
                .catch(function (err) { setNotesError(getErrorMessage(err)); });
        }

        function handleDeleteNote(id) {
            setNotesError("");
            runAction("bratko:notes.notes.delete", { id: id })
                .then(function () { loadNotes(); })
                .catch(function (err) { setNotesError(getErrorMessage(err)); });
        }

        function handleSaveNote(id, title, body) {
            setNotesError("");
            runAction("bratko:notes.notes.update", { id: id, title: title, content: body })
                .then(function () {
                    setEditModalOpen(false);
                    setEditingNote(null);
                    loadNotes();
                })
                .catch(function (err) { setNotesError(getErrorMessage(err)); });
        }

        if (!isAuthorized) {
            return h("div", { className: "bratko-notes-app", "data-authorized": "N" },
                h(AuthPage, {
                    messages: {
                        authTitle: messages.NOTES_LIST_AUTH_TITLE || "Мини-сервис заметок",
                        authSubtitle: messages.NOTES_LIST_AUTH_SUBTITLE || "идеи и напоминания",
                        authHint: messages.NOTES_LIST_AUTH_HINT || "Необходимо авторизоваться…",
                        placeholderLogin: messages.NOTES_AUTH_PLACEHOLDER_LOGIN || "Логин",
                        placeholderPassword: messages.NOTES_AUTH_PLACEHOLDER_PASSWORD || "Пароль",
                        authBtnSubmit: messages.NOTES_AUTH_BTN_SUBMIT || "Войти",
                        authRegisterHint: messages.NOTES_LIST_AUTH_REGISTER_HINT || "Новый пользователь?",
                        authRegisterBtn: messages.NOTES_LIST_AUTH_REGISTER_BTN || "Регистрация"
                    },
                    loginError: loginError,
                    onLoginSuccess: handleLoginSuccess,
                    onClearLoginError: function () { setLoginError(null); },
                    onOpenRegister: function () { setRegisterOpen(true); }
                }),
                h(RegisterModal, {
                    open: registerOpen,
                    registerError: registerError,
                    registerSuccess: registerSuccess,
                    timerSeconds: timerSeconds,
                    onCloseRegister: function () { setRegisterOpen(false); setRegisterError(null); },
                    onRegisterSuccess: handleRegisterSuccess,
                    messages: {
                        placeholderName: messages.NOTES_REGISTER_PLACEHOLDER_NAME || "Ваше имя",
                        placeholderLogin: messages.NOTES_REGISTER_PLACEHOLDER_LOGIN || "Логин",
                        placeholderEmail: messages.NOTES_REGISTER_PLACEHOLDER_EMAIL || "Email",
                        placeholderPassword: messages.NOTES_REGISTER_PLACEHOLDER_PASSWORD || "Пароль",
                        placeholderPasswordConfirm: messages.NOTES_REGISTER_PLACEHOLDER_PASSWORD_CONFIRM || "Повторите пароль",
                        btnCancel: messages.NOTES_REGISTER_BTN_CANCEL || "Отмена",
                        btnSubmit: messages.NOTES_REGISTER_BTN_SUBMIT || "Зарегистрироваться"
                    }
                }),
                h(DeveloperFooter)
            );
        }

        return h("div", { className: "bratko-notes-app", "data-authorized": "Y" },
            h(NotesPage, {
                userName: init.userName || "",
                logoutUrl: init.logoutUrl || "",
                messages: {
                    headerTitle: messages.NOTES_LIST_HEADER_TITLE || "Заметки",
                    headerSubtitle: messages.NOTES_LIST_HEADER_SUBTITLE || "Добавляйте идеи и напоминания",
                    headerLogout: messages.NOTES_LIST_HEADER_LOGOUT || "Выйти",
                    placeholderTitle: messages.NOTES_LIST_PLACEHOLDER_TITLE || "Заголовок",
                    placeholderBody: messages.NOTES_LIST_PLACEHOLDER_BODY || "Текст заметки…",
                    addBtn: messages.NOTES_LIST_ADD_BTN || "Добавить",
                    addNoteLabel: messages.NOTES_LIST_ADD_NOTE_LABEL || "Добавить заметку",
                    emptyText: messages.NOTES_LIST_EMPTY_TEXT || "Пока нет заметок",
                    emptyHint: messages.NOTES_LIST_EMPTY_HINT || "Добавьте первую — заголовок и текст выше",
                    listLabel: messages.NOTES_LIST_LIST_LABEL || "Список заметок",
                    footerCount: messages.NOTES_LIST_FOOTER_COUNT || "0 заметок",
                    btnEditLabel: messages.NOTES_LIST_BTN_EDIT_LABEL || "Редактировать",
                    btnDeleteLabel: messages.NOTES_LIST_BTN_DELETE_LABEL || "Удалить",
                    editModalTitle: messages.NOTES_LIST_EDIT_MODAL_TITLE || "Редактировать заметку",
                    editPlaceholderBody: messages.NOTES_LIST_EDIT_PLACEHOLDER_BODY || "Текст заметки",
                    modalCancel: messages.NOTES_LIST_MODAL_CANCEL || "Отмена",
                    modalSave: messages.NOTES_LIST_MODAL_SAVE || "Сохранить"
                },
                notes: notes,
                error: notesError,
                loading: notesLoading,
                onAddNote: handleAddNote,
                onEditNote: function (note) { setEditingNote(note); setEditModalOpen(true); },
                onDeleteNote: handleDeleteNote,
                onSaveNote: handleSaveNote,
                onCloseEditModal: function () { setEditModalOpen(false); setEditingNote(null); },
                editModalOpen: editModalOpen,
                editingNote: editingNote
            }),
            h(DeveloperFooter)
        );
    }

    function mount() {
        console.log("[bratko.notes] 3. Вызов mount()");
        var rootEl = document.getElementById("bratko-notes-app");
        if (!rootEl) {
            console.error("[bratko.notes] Элемент #bratko-notes-app не найден в DOM. Скрипт мог выполниться до появления разметки.");
            return;
        }
        console.log("[bratko.notes] 4. BRATKO_NOTES_INIT =", global.BRATKO_NOTES_INIT);
        try {
            console.log("[bratko.notes] 5. Запуск React render...");
            if (ReactDOM.createRoot) {
                var root = ReactDOM.createRoot(rootEl);
                root.render(h(App));
            } else {
                ReactDOM.render(h(App), rootEl);
            }
            console.log("[bratko.notes] 6. Render завершён без исключения");
        } catch (err) {
            console.error("[bratko.notes] Ошибка при монтировании React:", err);
            rootEl.innerHTML = "<div style=\"padding:1.5rem;font-family:system-ui;color:#2c2825;background:#fffefb;border:1px solid #e8e4de;border-radius:14px;max-width:480px;margin:1rem;\"><p style=\"margin:0 0 0.5rem;font-weight:600;\">Ошибка загрузки приложения заметок</p></div>";
        }
    }

    if (global.BX && global.BX.ready) {
        console.log("[bratko.notes] Ожидание BX.ready...");
        global.BX.ready(mount);
    } else if (document.readyState !== "loading") {
        mount();
    } else {
        document.addEventListener("DOMContentLoaded", mount);
    }
})(window);
