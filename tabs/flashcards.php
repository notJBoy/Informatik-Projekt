<!-- =============================================
     Karteikarten Tab
     ============================================= -->
<style>
    /* ---- FC Sub-Navigation ---- */
    .fc-subnav {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1.75rem;
        border-bottom: 2px solid var(--color-border);
        padding-bottom: 0;
    }
    .fc-subnav-btn {
        padding: 0.6rem 1.25rem;
        background: transparent;
        border: none;
        border-bottom: 2px solid transparent;
        margin-bottom: -2px;
        cursor: pointer;
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--color-text-secondary);
        transition: all 0.2s;
    }
    .fc-subnav-btn.active {
        color: var(--color-primary);
        border-bottom-color: var(--color-primary);
    }
    .fc-subnav-btn:hover:not(.active) {
        color: var(--color-text-primary);
    }

    /* ---- Deck Grid ---- */
    .fc-decks-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 1.25rem;
        margin-top: 1rem;
    }
    .fc-deck-card {
        background: var(--color-bg-surface);
        border: 1px solid var(--color-border);
        border-radius: 12px;
        padding: 1.25rem;
        cursor: pointer;
        transition: all 0.25s;
        box-shadow: var(--shadow-sm);
        position: relative;
    }
    .fc-deck-card:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
        border-color: var(--color-primary);
    }
    .fc-deck-name {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--color-text-primary);
        margin-bottom: 0.35rem;
    }
    .fc-deck-subject {
        font-size: 0.82rem;
        color: var(--color-primary);
        font-weight: 500;
        margin-bottom: 0.5rem;
    }
    .fc-deck-meta {
        font-size: 0.8rem;
        color: var(--color-text-muted);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    .fc-badge {
        font-size: 0.7rem;
        padding: 0.15rem 0.55rem;
        border-radius: 20px;
        font-weight: 600;
        background: rgba(16, 185, 129, 0.15);
        color: var(--color-success);
    }
    .fc-badge.private {
        background: rgba(108, 117, 125, 0.15);
        color: var(--color-text-secondary);
    }
    .fc-deck-actions {
        position: absolute;
        top: 1rem;
        right: 1rem;
        display: flex;
        gap: 0.3rem;
        opacity: 0;
        transition: opacity 0.2s;
    }
    .fc-deck-card:hover .fc-deck-actions {
        opacity: 1;
    }
    .fc-deck-action-btn {
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--color-border);
        border-radius: 6px;
        background: var(--color-bg-surface);
        cursor: pointer;
        font-size: 0.8rem;
        transition: all 0.2s;
        z-index: 1;
    }
    .fc-deck-action-btn:hover { background: var(--color-bg-hover); }
    .fc-deck-action-btn.danger:hover { background: rgba(239,68,68,0.1); border-color: var(--color-danger); }

    /* ---- New Deck Form ---- */
    .fc-new-deck-form {
        background: var(--color-bg-surface);
        border: 1px dashed var(--color-border);
        border-radius: 12px;
        padding: 1.25rem;
        display: none;
        flex-direction: column;
        gap: 0.75rem;
        margin-bottom: 1.25rem;
    }
    .fc-new-deck-form.open {
        display: flex;
    }
    .fc-form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem;
    }
    .fc-input {
        width: 100%;
        padding: 0.65rem 0.85rem;
        border: 1px solid var(--color-border);
        border-radius: 8px;
        background: var(--color-bg-primary);
        color: var(--color-text-primary);
        font-size: 0.9rem;
        box-sizing: border-box;
    }
    .fc-input:focus { outline: none; border-color: var(--color-primary); }
    .fc-input::placeholder { color: var(--color-text-muted); }
    .fc-textarea {
        resize: vertical;
        min-height: 72px;
    }
    .fc-form-actions {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }
    .fc-checkbox-label {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.85rem;
        color: var(--color-text-secondary);
        cursor: pointer;
        margin-left: auto;
    }

    /* ---- Deck Detail View ---- */
    .fc-detail-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }
    .fc-back-btn {
        padding: 0.5rem 1rem;
        background: transparent;
        border: 1px solid var(--color-border);
        border-radius: 8px;
        cursor: pointer;
        font-size: 0.9rem;
        color: var(--color-text-secondary);
        transition: all 0.2s;
    }
    .fc-back-btn:hover { background: var(--color-bg-hover); color: var(--color-text-primary); }
    .fc-detail-title {
        font-size: 1.5rem;
        font-weight: 700;
        flex: 1;
    }
    .fc-detail-subject-badge {
        font-size: 0.82rem;
        padding: 0.2rem 0.65rem;
        background: rgba(13,110,253,0.12);
        color: var(--color-primary);
        border-radius: 20px;
        font-weight: 600;
    }

    /* ---- Cards List ---- */
    .fc-cards-list {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
        max-height: 400px;
        overflow-y: auto;
    }
    .fc-card-item {
        display: flex;
        align-items: stretch;
        background: var(--color-bg-hover);
        border: 1px solid var(--color-border);
        border-radius: 10px;
        overflow: hidden;
        transition: all 0.2s;
    }
    .fc-card-item:hover {
        border-color: var(--color-primary);
        box-shadow: var(--shadow-sm);
    }
    .fc-card-front {
        flex: 1;
        padding: 0.75rem 1rem;
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--color-text-primary);
        border-right: 1px solid var(--color-border);
        word-break: break-word;
    }
    .fc-card-back {
        flex: 1;
        padding: 0.75rem 1rem;
        font-size: 0.9rem;
        color: var(--color-text-secondary);
        word-break: break-word;
    }
    .fc-card-del {
        padding: 0 0.75rem;
        background: transparent;
        border: none;
        cursor: pointer;
        color: var(--color-text-muted);
        font-size: 0.9rem;
        transition: all 0.2s;
        flex-shrink: 0;
    }
    .fc-card-del:hover { color: var(--color-danger); background: rgba(239,68,68,0.07); }

    /* ---- Add Card Form ---- */
    .fc-add-card-form {
        background: var(--color-bg-surface);
        border: 1px solid var(--color-border);
        border-radius: 12px;
        padding: 1.25rem;
        margin-bottom: 1.5rem;
    }
    .fc-add-card-title {
        font-size: 0.95rem;
        font-weight: 600;
        margin-bottom: 0.85rem;
        color: var(--color-text-primary);
    }
    .fc-add-card-row {
        display: grid;
        grid-template-columns: 1fr 1fr auto;
        gap: 0.75rem;
        align-items: start;
    }
    @media (max-width: 600px) {
        .fc-add-card-row { grid-template-columns: 1fr; }
        .fc-form-row { grid-template-columns: 1fr; }
    }

    /* ---- Study Mode ---- */
    .fc-study-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1.75rem;
        flex-wrap: wrap;
    }
    .fc-study-progress {
        margin-left: auto;
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--color-text-secondary);
    }
    .fc-study-progress-bar {
        height: 6px;
        background: var(--color-border);
        border-radius: 3px;
        margin-bottom: 2rem;
        overflow: hidden;
    }
    .fc-study-progress-fill {
        height: 100%;
        background: var(--color-primary);
        border-radius: 3px;
        transition: width 0.4s ease;
    }
    .fc-study-card-wrap {
        perspective: 1000px;
        max-width: 640px;
        height: 260px;
        margin: 0 auto 2rem;
        cursor: pointer;
    }
    .fc-study-card-inner {
        position: relative;
        width: 100%;
        height: 100%;
        transition: transform 0.55s ease;
        transform-style: preserve-3d;
    }
    .fc-study-card-wrap.flipped .fc-study-card-inner {
        transform: rotateY(180deg);
    }
    .fc-study-face {
        position: absolute;
        inset: 0;
        backface-visibility: hidden;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
        text-align: center;
        font-size: 1.15rem;
    }
    .fc-study-front {
        background: var(--color-bg-surface);
        border: 2px solid var(--color-border);
        box-shadow: var(--shadow-md);
        flex-direction: column;
        gap: 0.5rem;
        position: relative;
    }
    .fc-study-hint {
        position: absolute;
        bottom: 0.85rem;
        font-size: 0.72rem;
        color: var(--color-text-muted);
        font-style: italic;
    }
    .fc-study-back {
        background: linear-gradient(135deg, var(--color-primary), var(--color-primary-hover));
        color: white;
        transform: rotateY(180deg);
        box-shadow: var(--shadow-md);
    }
    .fc-study-nav {
        display: flex;
        justify-content: center;
        gap: 1rem;
        align-items: center;
        flex-wrap: wrap;
    }
    .fc-study-btn {
        padding: 0.7rem 1.75rem;
        background: var(--color-primary);
        color: white;
        border: none;
        border-radius: 9px;
        cursor: pointer;
        font-size: 0.95rem;
        font-weight: 600;
        transition: all 0.2s;
    }
    .fc-study-btn:hover { background: var(--color-primary-hover); transform: translateY(-1px); }
    .fc-study-btn.secondary {
        background: transparent;
        color: var(--color-text-secondary);
        border: 1px solid var(--color-border);
    }
    .fc-study-btn.secondary:hover { background: var(--color-bg-hover); color: var(--color-text-primary); }

    /* ---- Explore Grid ---- */
    .fc-explore-search {
        display: flex;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        align-items: center;
    }
    .fc-explore-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.25rem;
    }
    .fc-explore-card {
        background: var(--color-bg-surface);
        border: 1px solid var(--color-border);
        border-radius: 12px;
        padding: 1.25rem;
        box-shadow: var(--shadow-sm);
        display: flex;
        flex-direction: column;
        gap: 0.6rem;
        transition: all 0.25s;
    }
    .fc-explore-card:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
    }
    .fc-explore-name {
        font-size: 1rem;
        font-weight: 700;
        color: var(--color-text-primary);
    }
    .fc-explore-author {
        font-size: 0.8rem;
        color: var(--color-text-muted);
    }
    .fc-explore-desc {
        font-size: 0.85rem;
        color: var(--color-text-secondary);
        flex: 1;
    }
    .fc-explore-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 0.25rem;
        gap: 0.5rem;
    }
    .fc-explore-count { font-size: 0.8rem; color: var(--color-text-muted); }
    .fc-hint { text-align: center; color: var(--color-text-muted); padding: 2.5rem 1rem; font-size: 0.95rem; }
    .fc-loading { text-align: center; padding: 3rem 1rem; color: var(--color-text-muted); }
</style>

<div id="flashcards" class="view-content" style="display: none;">
    <div class="content-header">
        <h1>🎴 Karteikarten</h1>
        <p>Erstelle eigene Stapel und entdecke öffentliche Karteikarten</p>
    </div>

    <!-- Sub-Navigation -->
    <div class="fc-subnav">
        <button class="fc-subnav-btn active" onclick="fcShowTab('my-decks', this)">📚 Meine Stapel</button>
        <button class="fc-subnav-btn" onclick="fcShowTab('explore', this)">🌐 Erkunden</button>
    </div>

    <!-- ================================================
         VIEW: Meine Stapel
         ================================================ -->
    <div id="fc-view-my-decks" class="fc-subview">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem; flex-wrap:wrap; gap:0.5rem;">
            <span id="fc-decks-count" style="font-size:0.9rem; color:var(--color-text-muted);"></span>
            <button class="btn-primary" onclick="fcToggleNewDeckForm()">+ Neuer Stapel</button>
        </div>

        <!-- New deck form -->
        <div class="fc-new-deck-form" id="fc-new-deck-form">
            <div class="fc-form-row">
                <input class="fc-input" id="fc-new-name" placeholder="Stapelname *" />
                <input class="fc-input" id="fc-new-subject" placeholder="Fach (z.B. Mathematik)" />
            </div>
            <textarea class="fc-input fc-textarea" id="fc-new-desc" placeholder="Kurze Beschreibung (optional)"></textarea>
            <div class="fc-form-actions">
                <button class="btn-primary" onclick="fcCreateDeck()">Erstellen</button>
                <button class="btn-secondary" onclick="fcToggleNewDeckForm()">Abbrechen</button>
                <label class="fc-checkbox-label">
                    <input type="checkbox" id="fc-new-public" />
                    Öffentlich sichtbar
                </label>
            </div>
        </div>

        <!-- Deck Grid -->
        <div class="fc-decks-grid" id="fc-decks-grid">
            <div class="fc-loading">⏳ Lade Stapel…</div>
        </div>
    </div>

    <!-- ================================================
         VIEW: Deck Detail
         ================================================ -->
    <div id="fc-view-deck-detail" class="fc-subview" style="display:none;">
        <div class="fc-detail-header">
            <button class="fc-back-btn" onclick="fcShowDecksView()">← Zurück</button>
            <div class="fc-detail-title" id="fc-detail-title">Stapelname</div>
            <span class="fc-detail-subject-badge" id="fc-detail-subject" style="display:none;"></span>
            <button class="fc-study-btn" style="margin-left:auto;" id="fc-start-study-btn" onclick="fcStartStudy()">🎓 Lernen</button>
        </div>

        <!-- Deck options bar -->
        <div style="display:flex; gap:0.5rem; margin-bottom:1.5rem; align-items:center; flex-wrap:wrap;">
            <span id="fc-detail-visibility-badge" class="fc-badge private">🔒 Privat</span>
            <button class="btn-secondary" style="padding:0.45rem 1rem; font-size:0.85rem;" onclick="fcToggleDeckPublic()">Sichtbarkeit ändern</button>
            <button class="btn-secondary" style="padding:0.45rem 1rem; font-size:0.85rem; color:var(--color-danger); border-color:var(--color-danger); margin-left:auto;" onclick="fcDeleteCurrentDeck()">🗑️ Stapel löschen</button>
        </div>

        <!-- Add card form -->
        <div class="fc-add-card-form">
            <div class="fc-add-card-title">➕ Neue Karte hinzufügen</div>
            <div class="fc-add-card-row">
                <textarea class="fc-input fc-textarea" style="min-height:80px;" id="fc-card-front" placeholder="Vorderseite (Frage / Begriff)"></textarea>
                <textarea class="fc-input fc-textarea" style="min-height:80px;" id="fc-card-back" placeholder="Rückseite (Antwort / Definition)"></textarea>
                <button class="btn-primary" style="padding:0.75rem 1.25rem; height:80px;" onclick="fcAddCard()">Karte<br>hinzufügen</button>
            </div>
        </div>

        <!-- Cards list -->
        <div style="font-size:0.9rem; font-weight:600; color:var(--color-text-secondary); margin-bottom:0.65rem;">
            Karten in diesem Stapel (<span id="fc-cards-count">0</span>)
        </div>
        <div class="fc-cards-list" id="fc-cards-list">
            <div class="fc-hint">Noch keine Karten. Füge Karten oben hinzu!</div>
        </div>
    </div>

    <!-- ================================================
         VIEW: Study Mode
         ================================================ -->
    <div id="fc-view-study" class="fc-subview" style="display:none;">
        <div class="fc-study-header">
            <button class="fc-back-btn" onclick="fcStopStudy()">← Zum Stapel</button>
            <span id="fc-study-deck-name" style="font-size:1.1rem; font-weight:700;"></span>
            <span class="fc-study-progress" id="fc-study-progress">1 / 1</span>
        </div>
        <div class="fc-study-progress-bar">
            <div class="fc-study-progress-fill" id="fc-study-progress-fill" style="width:0%"></div>
        </div>
        <div class="fc-study-card-wrap" id="fc-study-card-wrap" onclick="fcStudyFlip()">
            <div class="fc-study-card-inner">
                <div class="fc-study-face fc-study-front">
                    <span id="fc-study-front-content" style="font-size:1.15rem; font-weight:500;"></span>
                    <span class="fc-study-hint">Tippe zum Umdrehen</span>
                </div>
                <div class="fc-study-face fc-study-back" id="fc-study-back-text"></div>
            </div>
        </div>
        <div class="fc-study-nav" id="fc-study-nav">
            <button class="fc-study-btn secondary" onclick="fcStudyPrev()">← Zurück</button>
            <button class="fc-study-btn secondary" onclick="fcStudyFlip()">🔄 Umdrehen</button>
            <button class="fc-study-btn" onclick="fcStudyNext()">Weiter →</button>
        </div>
        <div id="fc-study-finish" style="display:none; text-align:center; margin-top:2rem;">
            <div style="font-size:2.5rem; margin-bottom:0.75rem;">🎉</div>
            <div style="font-size:1.2rem; font-weight:700; margin-bottom:0.5rem;">Alle Karten durchgegangen!</div>
            <div style="color:var(--color-text-secondary); margin-bottom:1.5rem;">Super gemacht! Möchtest du nochmal von vorne beginnen?</div>
            <div style="display:flex; justify-content:center; gap:1rem; flex-wrap:wrap;">
                <button class="fc-study-btn" onclick="fcStudyRestart()">🔄 Nochmal</button>
                <button class="fc-study-btn secondary" onclick="fcStopStudy()">Zum Stapel</button>
            </div>
        </div>
    </div>

    <!-- ================================================
         VIEW: Erkunden (Explore)
         ================================================ -->
    <div id="fc-view-explore" class="fc-subview" style="display:none;">
        <div class="fc-explore-search">
            <input class="fc-input" id="fc-explore-search" placeholder="🔍  Stapel suchen nach Name, Fach, Nutzer…" oninput="fcFilterExplore()" style="max-width:380px;" />
            <button class="btn-secondary" onclick="fcLoadExplore()">🔄 Aktualisieren</button>
        </div>
        <div class="fc-explore-grid" id="fc-explore-grid">
            <div class="fc-loading">⏳ Lade öffentliche Stapel…</div>
        </div>
    </div>
</div>

<script>
// ==============================================
// Karteikarten – vollständige Logik
// ==============================================

let fcCurrentDeck = null;
let fcCurrentCards = [];
let fcStudyIndex = 0;
let fcAllDecks = [];
let fcExploreData = [];

// ---- Sub-tab switching ----
function fcShowTab(tab, btn) {
    document.querySelectorAll('.fc-subnav-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    if (tab === 'my-decks') {
        fcShowDecksView();
    } else if (tab === 'explore') {
        fcShowSubview('explore');
        fcLoadExplore();
    }
}

function fcShowSubview(name) {
    document.querySelectorAll('#flashcards .fc-subview').forEach(v => v.style.display = 'none');
    const el = document.getElementById('fc-view-' + name);
    if (el) el.style.display = 'block';
}

// ---- Load & Render Decks ----
async function fcLoadDecks() {
    try {
        const res = await fetch('flashcards/decks_load.php');
        fcAllDecks = res.ok ? await res.json() : [];
    } catch { fcAllDecks = []; }
    fcRenderDecks();
}

function fcRenderDecks() {
    const grid = document.getElementById('fc-decks-grid');
    const countEl = document.getElementById('fc-decks-count');
    if (!grid) return;
    countEl.textContent = fcAllDecks.length + ' Stapel';
    if (!fcAllDecks.length) {
        grid.innerHTML = '<div class="fc-hint" style="grid-column:1/-1;">Noch keine Stapel vorhanden.<br>Erstelle deinen ersten Stapel!</div>';
        return;
    }
    grid.innerHTML = fcAllDecks.map(function(d) {
        return '<div class="fc-deck-card" onclick="fcOpenDeck(\'' + fcEsc(d.id) + '\')">' +
            '<div class="fc-deck-actions" onclick="event.stopPropagation()">' +
                '<button class="fc-deck-action-btn danger" onclick="fcDeleteDeckById(\'' + fcEsc(d.id) + '\')" title="Löschen">🗑️</button>' +
            '</div>' +
            '<div class="fc-deck-name">' + fcEsc(d.name) + '</div>' +
            (d.subject ? '<div class="fc-deck-subject">' + fcEsc(d.subject) + '</div>' : '') +
            '<div class="fc-deck-meta">' +
                '<span>📑 ' + d.card_count + ' Karte' + (d.card_count !== 1 ? 'n' : '') + '</span>' +
                '<span class="fc-badge ' + (d.public ? '' : 'private') + '">' + (d.public ? '🌐 Öffentlich' : '🔒 Privat') + '</span>' +
            '</div>' +
        '</div>';
    }).join('');
}

function fcShowDecksView() {
    fcShowSubview('my-decks');
    fcLoadDecks();
}

// ---- New Deck Form ----
function fcToggleNewDeckForm() {
    var form = document.getElementById('fc-new-deck-form');
    form.classList.toggle('open');
    if (form.classList.contains('open')) {
        document.getElementById('fc-new-name').focus();
    }
}

async function fcCreateDeck() {
    var name = document.getElementById('fc-new-name').value.trim();
    var subject = document.getElementById('fc-new-subject').value.trim();
    var description = document.getElementById('fc-new-desc').value.trim();
    var isPublic = document.getElementById('fc-new-public').checked;
    if (!name) { alert('Bitte einen Namen eingeben.'); return; }
    try {
        var res = await fetch('flashcards/deck_create.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name: name, subject: subject, description: description, public: isPublic })
        });
        if (res.ok) {
            document.getElementById('fc-new-name').value = '';
            document.getElementById('fc-new-subject').value = '';
            document.getElementById('fc-new-desc').value = '';
            document.getElementById('fc-new-public').checked = false;
            document.getElementById('fc-new-deck-form').classList.remove('open');
            await fcLoadDecks();
        } else {
            var err = await res.json();
            alert(err.detail || 'Fehler beim Erstellen');
        }
    } catch(e) { alert('Server nicht erreichbar.'); }
}

// ---- Open a Deck ----
async function fcOpenDeck(deckId) {
    var deck = fcAllDecks.find(function(d) { return d.id === deckId; });
    if (!deck) return;
    fcCurrentDeck = deck;
    document.getElementById('fc-detail-title').textContent = deck.name;
    var subjectEl = document.getElementById('fc-detail-subject');
    if (deck.subject) {
        subjectEl.textContent = deck.subject;
        subjectEl.style.display = 'inline-block';
    } else {
        subjectEl.style.display = 'none';
    }
    fcUpdateVisibilityBadge(deck.public);
    fcShowSubview('deck-detail');
    await fcLoadCards();
}

function fcUpdateVisibilityBadge(isPublic) {
    var badge = document.getElementById('fc-detail-visibility-badge');
    if (isPublic) {
        badge.textContent = '🌐 Öffentlich';
        badge.classList.remove('private');
    } else {
        badge.textContent = '🔒 Privat';
        badge.classList.add('private');
    }
}

// ---- Load & Render Cards ----
async function fcLoadCards() {
    if (!fcCurrentDeck) return;
    try {
        var res = await fetch('flashcards/cards_load.php?deck_id=' + encodeURIComponent(fcCurrentDeck.id));
        fcCurrentCards = res.ok ? await res.json() : [];
    } catch { fcCurrentCards = []; }
    fcRenderCards();
}

function fcRenderCards() {
    var list = document.getElementById('fc-cards-list');
    var countEl = document.getElementById('fc-cards-count');
    if (!list) return;
    countEl.textContent = fcCurrentCards.length;
    var studyBtn = document.getElementById('fc-start-study-btn');
    if (studyBtn) studyBtn.disabled = fcCurrentCards.length === 0;

    if (!fcCurrentCards.length) {
        list.innerHTML = '<div class="fc-hint">Noch keine Karten. Füge Karten oben hinzu!</div>';
        return;
    }
    list.innerHTML = fcCurrentCards.map(function(c) {
        return '<div class="fc-card-item" id="fccard-' + fcEsc(c.id) + '">' +
            '<div class="fc-card-front">' + fcEsc(c.front) + '</div>' +
            '<div class="fc-card-back">' + fcEsc(c.back) + '</div>' +
            '<button class="fc-card-del" onclick="fcDeleteCard(\'' + fcEsc(c.id) + '\')" title="Löschen">✕</button>' +
        '</div>';
    }).join('');
}

async function fcAddCard() {
    var front = document.getElementById('fc-card-front').value.trim();
    var back = document.getElementById('fc-card-back').value.trim();
    if (!front || !back) { alert('Bitte Vorder- und Rückseite ausfüllen.'); return; }
    if (!fcCurrentDeck) return;
    try {
        var res = await fetch('flashcards/card_add.php?deck_id=' + encodeURIComponent(fcCurrentDeck.id), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ front: front, back: back })
        });
        if (res.ok) {
            document.getElementById('fc-card-front').value = '';
            document.getElementById('fc-card-back').value = '';
            await fcLoadCards();
        } else {
            var err = await res.json();
            alert(err.detail || 'Fehler');
        }
    } catch(e) { alert('Server nicht erreichbar.'); }
}

async function fcDeleteCard(cardId) {
    if (!confirm('Karte löschen?')) return;
    try {
        var res = await fetch('flashcards/card_delete.php?card_id=' + encodeURIComponent(cardId), { method: 'POST' });
        if (res.ok) {
            fcCurrentCards = fcCurrentCards.filter(function(c) { return c.id !== cardId; });
            fcRenderCards();
        }
    } catch(e) { alert('Server nicht erreichbar.'); }
}

async function fcDeleteCurrentDeck() {
    if (!fcCurrentDeck) return;
    if (!confirm('Stapel "' + fcCurrentDeck.name + '" wirklich löschen? Alle Karten werden gelöscht.')) return;
    await fcDeleteDeckById(fcCurrentDeck.id);
    fcShowDecksView();
}

async function fcDeleteDeckById(deckId) {
    try {
        var res = await fetch('flashcards/deck_delete.php?deck_id=' + encodeURIComponent(deckId), { method: 'POST' });
        if (res.ok) {
            fcAllDecks = fcAllDecks.filter(function(d) { return d.id !== deckId; });
            fcRenderDecks();
        } else {
            alert('Fehler beim Löschen.');
        }
    } catch(e) { alert('Server nicht erreichbar.'); }
}

async function fcToggleDeckPublic() {
    if (!fcCurrentDeck) return;
    var newPublic = !fcCurrentDeck.public;
    try {
        var res = await fetch('flashcards/deck_update.php?deck_id=' + encodeURIComponent(fcCurrentDeck.id), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ public: newPublic })
        });
        if (res.ok) {
            fcCurrentDeck.public = newPublic;
            fcUpdateVisibilityBadge(newPublic);
            var deckInList = fcAllDecks.find(function(d) { return d.id === fcCurrentDeck.id; });
            if (deckInList) deckInList.public = newPublic;
        }
    } catch(e) { alert('Server nicht erreichbar.'); }
}

// ---- Study Mode ----
function fcStartStudy() {
    if (!fcCurrentCards.length) return;
    fcStudyIndex = 0;
    document.getElementById('fc-study-deck-name').textContent = fcCurrentDeck.name;
    document.getElementById('fc-study-finish').style.display = 'none';
    document.getElementById('fc-study-card-wrap').style.display = 'block';
    document.getElementById('fc-study-card-wrap').classList.remove('flipped');
    document.getElementById('fc-study-nav').style.display = 'flex';
    fcUpdateStudyCard();
    fcShowSubview('study');
}

function fcUpdateStudyCard() {
    var card = fcCurrentCards[fcStudyIndex];
    var total = fcCurrentCards.length;
    document.getElementById('fc-study-progress').textContent = (fcStudyIndex + 1) + ' / ' + total;
    var pct = ((fcStudyIndex + 1) / total) * 100;
    document.getElementById('fc-study-progress-fill').style.width = pct + '%';
    document.getElementById('fc-study-front-content').textContent = card.front;
    document.getElementById('fc-study-back-text').textContent = card.back;
    document.getElementById('fc-study-card-wrap').classList.remove('flipped');
}

function fcStudyFlip() {
    document.getElementById('fc-study-card-wrap').classList.toggle('flipped');
}

function fcStudyNext() {
    if (fcStudyIndex < fcCurrentCards.length - 1) {
        fcStudyIndex++;
        fcUpdateStudyCard();
    } else {
        document.getElementById('fc-study-card-wrap').style.display = 'none';
        document.getElementById('fc-study-nav').style.display = 'none';
        document.getElementById('fc-study-finish').style.display = 'block';
        document.getElementById('fc-study-progress').textContent = fcCurrentCards.length + ' / ' + fcCurrentCards.length;
        document.getElementById('fc-study-progress-fill').style.width = '100%';
    }
}

function fcStudyPrev() {
    if (fcStudyIndex > 0) {
        fcStudyIndex--;
        fcUpdateStudyCard();
    }
}

function fcStudyRestart() {
    fcStudyIndex = 0;
    document.getElementById('fc-study-finish').style.display = 'none';
    document.getElementById('fc-study-card-wrap').style.display = 'block';
    document.getElementById('fc-study-nav').style.display = 'flex';
    fcUpdateStudyCard();
}

function fcStopStudy() {
    fcShowSubview('deck-detail');
}

// ---- Explore ----
async function fcLoadExplore() {
    var grid = document.getElementById('fc-explore-grid');
    if (grid) grid.innerHTML = '<div class="fc-loading">⏳ Lade öffentliche Stapel…</div>';
    try {
        var res = await fetch('flashcards/explore_load.php');
        fcExploreData = res.ok ? await res.json() : [];
    } catch(e) { fcExploreData = []; }
    fcRenderExplore(fcExploreData);
}

function fcRenderExplore(decks) {
    var grid = document.getElementById('fc-explore-grid');
    if (!grid) return;
    if (!decks.length) {
        grid.innerHTML = '<div class="fc-hint" style="grid-column:1/-1;">Noch keine öffentlichen Stapel vorhanden.</div>';
        return;
    }
    grid.innerHTML = decks.map(function(d) {
        return '<div class="fc-explore-card">' +
            '<div>' +
                '<div class="fc-explore-name">' + fcEsc(d.name) + '</div>' +
                '<div class="fc-explore-author">von ' + fcEsc(d.username) + '</div>' +
                (d.subject ? '<span style="font-size:0.78rem;color:var(--color-primary);font-weight:600;">' + fcEsc(d.subject) + '</span>' : '') +
            '</div>' +
            '<div class="fc-explore-desc">' + (d.description ? fcEsc(d.description) : '<span style="font-style:italic;color:var(--color-text-muted);">Keine Beschreibung</span>') + '</div>' +
            '<div class="fc-explore-footer">' +
                '<span class="fc-explore-count">📑 ' + d.card_count + ' Karte' + (d.card_count !== 1 ? 'n' : '') + '</span>' +
                '<button class="btn-primary" style="padding:0.4rem 0.9rem; font-size:0.82rem;" onclick="fcCopyDeck(\'' + fcEsc(d.id) + '\', \'' + fcEsc(d.name) + '\')">Übernehmen</button>' +
            '</div>' +
        '</div>';
    }).join('');
}

function fcFilterExplore() {
    var q = document.getElementById('fc-explore-search').value.toLowerCase();
    if (!q) { fcRenderExplore(fcExploreData); return; }
    var filtered = fcExploreData.filter(function(d) {
        return d.name.toLowerCase().includes(q) ||
               (d.subject || '').toLowerCase().includes(q) ||
               (d.description || '').toLowerCase().includes(q) ||
               d.username.toLowerCase().includes(q);
    });
    fcRenderExplore(filtered);
}

async function fcCopyDeck(deckId, deckName) {
    try {
        var res = await fetch('flashcards/deck_copy.php?deck_id=' + encodeURIComponent(deckId), { method: 'POST' });
        if (res.ok) {
            alert('Stapel "' + deckName + '" wurde zu deinen Stapeln hinzugefügt!');
            await fcLoadDecks();
        } else {
            var err = await res.json();
            alert(err.detail || 'Fehler beim Übernehmen');
        }
    } catch(e) { alert('Server nicht erreichbar.'); }
}

// ---- HTML escaping ----
function fcEsc(str) {
    if (str == null) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

// ---- Hook into dashboard navigation ----
(function() {
    function fcInitHook() {
        var navItem = document.querySelector('.nav-item[data-view="flashcards"]');
        if (navItem) {
            navItem.addEventListener('click', function() {
                setTimeout(function() {
                    if (document.getElementById('fc-view-my-decks') &&
                        document.getElementById('fc-view-my-decks').style.display !== 'none') {
                        fcLoadDecks();
                    }
                }, 50);
            });
        }
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fcInitHook);
    } else {
        fcInitHook();
    }
})();
</script>
