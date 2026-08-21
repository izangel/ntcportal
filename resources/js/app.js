import './bootstrap';

window.syllabusTour = () => ({
    active: false,
    step: 0,
    rect: null,
    steps: [
        { id: 'section-checklist', title: 'Start here — the checklist', desc: 'This panel lists every section of the syllabus in the order you should complete it. A tick appears automatically as you finish each part.' },
        { id: 'section-copo', title: 'Course Outcomes & CO-PO Mapping', desc: 'Review the course outcomes for this block and confirm each one is mapped to a program outcome in the matrix.' },
        { id: 'section-tasks', title: 'Assessment Tasks', desc: 'Create your assessment tasks (quiz, exam, assignment, project…) and map each item to a CLO. Task weights must total 100%.' },
        { id: 'section-grading', title: 'Grading System (auto-computed)', desc: 'This is your grade recipe, derived automatically from your assessment tasks grouped by type. Keep task weights totaling 100% and this table follows.' },
        { id: 'section-textbooks', title: 'Textbooks & References', desc: 'List the textbooks and reference materials students will use for this course.' },
        { id: 'section-requirements', title: 'Course Requirements', desc: 'Add any attendance and other requirements students must meet to pass the course.' },
        { id: 'section-policies', title: 'Class Policies', desc: 'Describe grading, late submissions, academic integrity and other classroom policies.' },
        { id: 'section-learning-plan', title: 'Learning Plan', desc: 'Build the session-by-session plan with dates, topics, activities and the outcomes each session covers.' },
        { id: 'section-submit', title: 'Save & Submit', desc: 'Save a draft anytime with the buttons here. Once every mandatory rule passes, submit the syllabus for review.' },
    ],
    init() {
        const update = () => {
            if (this.active && this.rect) {
                const el = document.getElementById(this.current.id);
                if (el) this.rect = el.getBoundingClientRect();
            }
        };
        window.addEventListener('scroll', update, { passive: true });
        window.addEventListener('resize', update, { passive: true });
        this._update = update;
    },
    destroy() {
        if (this._update) {
            window.removeEventListener('scroll', this._update);
            window.removeEventListener('resize', this._update);
        }
    },
    get current() { return this.steps[this.step] || this.steps[0]; },
    get overlayClip() {
        const r = this.rect;
        if (!r) return 'none';
        const pad = 8;
        const top = r.top - pad, left = r.left - pad, right = r.right + pad, bottom = r.bottom + pad;
        return `polygon(0 0, 100vw 0, 100vw 100vh, 0 100vh, 0 ${top}px, ${left}px ${top}px, ${left}px ${bottom}px, ${right}px ${bottom}px, ${right}px ${top}px, 0 ${top}px)`;
    },
    get tipStyle() {
        const r = this.rect;
        if (!r) return { display: 'none' };
        const w = 320;
        let top = r.bottom + 12;
        if (top + 220 > window.innerHeight) top = Math.max(12, r.top - 12 - 220);
        const left = Math.min(Math.max(12, r.left), window.innerWidth - w - 12);
        return { position: 'fixed', top: top + 'px', left: left + 'px', width: w + 'px', zIndex: 50 };
    },
    start() {
        this.active = true;
        this.step = 0;
        this.$nextTick(() => this.place());
    },
    stop() {
        this.active = false;
        this.rect = null;
        this.highlight(false);
    },
    next() {
        if (this.step < this.steps.length - 1) {
            this.step++;
            this.$nextTick(() => this.place());
        } else {
            this.stop();
        }
    },
    back() {
        if (this.step > 0) {
            this.step--;
            this.$nextTick(() => this.place());
        }
    },
    place() {
        this.highlight(false);
        const el = document.getElementById(this.current.id);
        if (!el) { this.rect = null; return; }
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        setTimeout(() => {
            this.rect = el.getBoundingClientRect();
            this.highlight(true);
        }, 480);
    },
    highlight(on) {
        const el = document.getElementById(this.current.id);
        if (!el) return;
        if (on) {
            el.style.outline = '4px solid rgba(79, 70, 229, 0.9)';
            el.style.outlineOffset = '4px';
        } else {
            el.style.outline = '';
            el.style.outlineOffset = '';
        }
    },
});