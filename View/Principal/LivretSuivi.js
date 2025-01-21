// On met tout en global(let)
console.log("LivretSuivi.js loaded");

let meetingIndexCounter = 1;
let dynamicSectionId = 100;
let qcmCounter=0, commCounter=0, qrCounter=0, textCounter=0;

// Au chargement, si on veut charger
document.addEventListener('DOMContentLoaded', () => {
    console.log("DOM loaded => check followUpId");
    if (typeof window.followUpId !== 'undefined' && window.followUpId > 0) {
        loadExistingMeetings();
    }
    bindBilanSave();
});

/** addMeeting() => crée bloc pour ce followUpId */
function addMeeting() {
    console.log("addMeeting() called!");
    const meetName = "Rencontre " + meetingIndexCounter;
    createMeetingBlock(meetName);
    meetingIndexCounter++;
}

/**
 * deleteMeeting() -- inline onclick
 */
function deleteMeeting() {
    console.log("deleteMeeting() called!");
    const sections = document.querySelectorAll('.content-section');
    let lastSec = null, count=0;
    sections.forEach(s => {
        if (s.id !== 'BilanSection') {
            lastSec = s;
            count++;
        }
    });
    if (count <= 1) {
        alert('Impossible de supprimer la première rencontre');
        return;
    }
    if (lastSec) {
        lastSec.remove();
        meetingIndexCounter--;
        dynamicSectionId--;
        console.log("Dernière rencontre supprimée");
    }
}

/**
 * createMeetingBlock
 */
function createMeetingBlock(meetName) {
    console.log("createMeetingBlock() for:", meetName);

    let container = document.querySelector('.content-livret');
    if (!container) {
        container = document.querySelector('.livret-container');
    }
    if (!container) {
        console.warn("Aucun conteneur .content-livret ou .livret-container pour insérer la rencontre!");
        return;
    }

    const sec = document.createElement('div');
    sec.className = 'content-section';
    const localId = dynamicSectionId++;
    sec.id = `section-${localId}`;

    sec.innerHTML = `
      <h3>${meetName}</h3>
      <form id="meetingForm-${localId}" style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
        <input type="hidden" name="followup_id" value="${window.followUpId||''}">

        <label>Date de la rencontre:</label><br>
        <input type="date" name="meeting"><br><br>

        <label>Date de fin de la rencontre:</label><br>
        <input type="date" name="end_meeting"><br><br>

        <label>Lieu de la rencontre:</label><br>
        <input type="radio" name="Lieu" value="Entreprise">Entreprise
        <input type="radio" name="Lieu" value="Tel">Téléphone
        <input type="radio" name="Lieu" value="Visio">Visio
        <input type="radio" name="Lieu" value="IUT">IUT
        <br><br>

        <button type="button" onclick="addMultiField(${localId})">
          + Ajouter un champ
        </button>
        <br><br>

        <button type="button" style="background:#0b5394; color:white; padding:6px 12px;"
                onclick="saveMeeting(${localId})">
          Valider les modifications
        </button>
      </form>
    `;
    const bilanSec = document.getElementById('BilanSection');
    if (bilanSec) {
        container.insertBefore(sec, bilanSec);
    } else {
        container.appendChild(sec);
    }
    console.log(`createMeetingBlock => bloc inséré, localId=${localId}`);
    return localId;
}

/**
 * addMultiField(blockId)
 */
function addMultiField(blockId) {
    console.log("addMultiField() called for blockId=", blockId);
    const form = document.getElementById(`meetingForm-${blockId}`);
    if (!form) {
        console.warn("Formulaire introuvable for blockId=", blockId);
        return;
    }
    const p = document.createElement('p');
    p.innerHTML = `
      <select>
        <option value="">Choisir le type</option>
        <option value="comm">Commentaire</option>
        <option value="qcm">QCM</option>
        <option value="qr">Question/Réponse</option>
        <option value="texte">Texte libre</option>
      </select>
      <input type="text" placeholder="Titre...">
      <button type="button" class="btn-ok-type">OK</button>
      <button type="button" class="btn-cancel-type">Annuler</button>
    `;
    form.appendChild(p);

    const sel = p.querySelector('select');
    const inp = p.querySelector('input[type="text"]');
    const ok  = p.querySelector('.btn-ok-type');
    const cancel = p.querySelector('.btn-cancel-type');

    ok.addEventListener('click', () => {
        const v = sel.value;
        const t = inp.value.trim();
        if (!v || !t) {
            alert('Choisissez un type et un titre');
            return;
        }
        switch(v) {
            case 'comm':
                createCommentFormBlock(form, t);
                break;
            case 'qcm':
                createQcmFormBlock(form, t);
                break;
            case 'qr':
                createQrFormBlock(form, t);
                break;
            case 'texte':
                createTextFormBlock(form, t);
                break;
        }
        p.remove();
    });
    cancel.addEventListener('click', () => {
        p.remove();
    });
}


function createCommentFormBlock(form, title, response='') {
    const idx = commCounter++;
    const d = document.createElement('div');
    d.style.border="1px dashed #666";
    d.style.margin="5px";
    d.style.padding="5px";
    d.innerHTML=`
      <p><strong>Commentaire: ${title}</strong>
         <button type="button" style="float:right;" onclick="this.closest('div').remove()">X</button>
      </p>
      <textarea class="comm-ta" rows="2" cols="40">${response}</textarea>
      <input type="hidden" name="commentaires[${idx}][title]" value="${title}">
      <input type="hidden" name="commentaires[${idx}][response]" value="">
    `;
    // insertBefore le bouton "Valider"
    const valBtn = form.querySelector('button[onclick^="saveMeeting"]');
    form.insertBefore(d, valBtn);
}

function createQcmFormBlock(form, title, choicesStr='', otherChoice='') {
    const idx = qcmCounter++;
    const d = document.createElement('div');
    d.style.border="1px dotted #666";
    d.style.margin="5px";
    d.style.padding="5px";

    let choices = [];
    try {
        if (choicesStr) {
            choices = JSON.parse(choicesStr);
        }
    } catch(e){}

    d.innerHTML=`
      <p><strong>QCM: ${title}</strong>
         <button type="button" style="float:right;" onclick="this.closest('div').remove()">X</button>
      </p>
      <div class="qcm-opts"></div>
      <button type="button" class="add-opt-qcm">+ Option</button>
      <input type="hidden" name="qcm[${idx}][title]" value="${title}">
      <input type="hidden" name="qcm[${idx}][choices]" value="">
      <input type="hidden" name="qcm[${idx}][other_choice]" value="${otherChoice}">
    `;
    const valBtn = form.querySelector('button[onclick^="saveMeeting"]');
    form.insertBefore(d, valBtn);

    const qcmOpts = d.querySelector('.qcm-opts');
    choices.forEach(opt => {
        const dd = document.createElement('div');
        dd.innerHTML=`
          <label><input type="radio">${opt}</label>
          <button type="button" class="del-opt" style="color:red;">X</button>
        `;
        dd.querySelector('.del-opt').addEventListener('click', ()=> dd.remove());
        qcmOpts.appendChild(dd);
    });

    d.querySelector('.add-opt-qcm').addEventListener('click', ()=>{
        const dd = document.createElement('div');
        dd.innerHTML=`
          <input type="text" placeholder="Nouvelle option...">
          <button type="button" class="ok-opt">OK</button>
          <button type="button" class="cancel-opt">Annuler</button>
        `;
        qcmOpts.appendChild(dd);

        dd.querySelector('.ok-opt').addEventListener('click', ()=>{
            const val = dd.querySelector('input').value.trim();
            if (!val) { alert("Option vide!"); return; }
            dd.innerHTML=`
              <label><input type="radio">${val}</label>
              <button type="button" class="del-opt" style="color:red;">X</button>
            `;
            dd.querySelector('.del-opt').addEventListener('click', ()=> dd.remove());
        });
        dd.querySelector('.cancel-opt').addEventListener('click', ()=> dd.remove());
    });
}

function createQrFormBlock(form, title, response='') {
    const idx = qrCounter++;
    const d = document.createElement('div');
    d.style.border="1px dotted #666";
    d.style.margin="5px";
    d.style.padding="5px";

    d.innerHTML=`
      <p><strong>Question: ${title}</strong>
         <button type="button" style="float:right;" onclick="this.closest('div').remove()">X</button>
      </p>
      <textarea class="qr-ta" rows="2" cols="40">${response}</textarea>
      <input type="hidden" name="questrep[${idx}][title]" value="${title}">
      <input type="hidden" name="questrep[${idx}][response]" value="">
    `;
    const valBtn = form.querySelector('button[onclick^="saveMeeting"]');
    form.insertBefore(d, valBtn);
}

function createTextFormBlock(form, title, response='') {
    const idx = textCounter++;
    const d = document.createElement('div');
    d.style.border="1px dashed #aaa";
    d.style.margin="5px";
    d.style.padding="5px";

    d.innerHTML=`
      <p><strong>Texte: ${title}</strong>
         <button type="button" style="float:right;" onclick="this.closest('div').remove()">X</button>
      </p>
      <textarea class="textblock-ta" rows="2" cols="40">${response}</textarea>
      <input type="hidden" name="texts[${idx}][title]" value="${title}">
      <input type="hidden" name="texts[${idx}][response]" value="">
    `;
    const valBtn = form.querySelector('button[onclick^="saveMeeting"]');
    form.insertBefore(d, valBtn);
}

/**
 * saveMeeting(blockId)
 */
function saveMeeting(blockId) {
    console.log("saveMeeting() called, blockId=", blockId);
    const form = document.getElementById(`meetingForm-${blockId}`);
    if (!form) {
        console.warn("Pas de form meetingForm-"+blockId);
        return;
    }
    // QCM => insérer choix
    form.querySelectorAll('.qcm-opts').forEach(qcmBlock => {
        const parentWrapper = qcmBlock.closest('div');
        const hiddenChoices = parentWrapper.querySelector('input[name^="qcm"][name$="[choices]"]');
        if (hiddenChoices) {
            let arr = [];
            qcmBlock.querySelectorAll('label').forEach(lb => {
                arr.push(lb.textContent.trim());
            });
            hiddenChoices.value = JSON.stringify(arr);
        }
    });
    // Commentaires
    form.querySelectorAll('textarea.comm-ta').forEach(ta => {
        const hidden = ta.parentNode.querySelector('input[name^="commentaires"][name$="[response]"]');
        if (hidden) hidden.value = ta.value.trim();
    });
    // Q/R
    form.querySelectorAll('textarea.qr-ta').forEach(ta => {
        const hidden = ta.parentNode.querySelector('input[name^="questrep"][name$="[response]"]');
        if (hidden) hidden.value = ta.value.trim();
    });
    // Textes
    form.querySelectorAll('textarea.textblock-ta').forEach(ta => {
        const hidden = ta.parentNode.querySelector('input[name^="texts"][name$="[response]"]');
        if (hidden) hidden.value = ta.value.trim();
    });

    const fd = new FormData(form);
    const h3 = form.parentNode.querySelector('h3');
    let meetName = h3 ? h3.textContent : 'Rencontre';
    fd.append('action','save_meeting');
    fd.append('meeting_name', meetName);

    fetch('Professor.php', {
        method:'POST',
        body: fd,
        headers:{'X-Requested-With':'XMLHttpRequest'}
    })
        .then(r=>r.json())
        .then(data=>{
            if (data.status==='success') {
                alert(data.message || 'Rencontre enregistrée!');
                loadExistingMeetings();
            } else {
                alert(data.message||'Erreur');
            }
        })
        .catch(err=>{
            console.error(err);
            alert("Erreur AJAX save_meeting");
        });
}

/** loadExistingMeetings() => lit les rencontres pour window.followUpId */
function loadExistingMeetings() {
    if (!window.followUpId || window.followUpId<=0) {
        console.log("Pas de followUpId => pas de chargement");
        return;
    }
    fetch(`Professor.php?action=get_meetings&followup_id=${window.followUpId}`, {
        headers:{'X-Requested-With':'XMLHttpRequest'}
    })
        .then(r=>r.json())
        .then(data=>{
            if (data.status==='success') {
                console.log("Rencontres chargées:", data.meetings);
                clearMeetingSections();
                // => data.meetings.length
                meetingIndexCounter = (data.meetings.length) + 1;  // pour la prochaine
                data.meetings.forEach((mtg, idx)=>{
                    const blockId = createMeetingBlock(mtg.name || ("Rencontre "+(idx+1)));
                    fillMeetingBlock(blockId, mtg);
                });
            } else {
                console.warn("Erreur ou pas de rencontres:", data.message);
            }
        })
        .catch(err=>{
            console.error("Erreur loadExistingMeetings:", err);
        });
}

/** clearMeetingSections => supprime tous les blocs .content-section sauf #BilanSection */
function clearMeetingSections() {
    document.querySelectorAll('.content-section').forEach(sec => {
        if (sec.id !== 'BilanSection') {
            sec.remove();
        }
    });
    dynamicSectionId=100;
    qcmCounter=0; commCounter=0; qrCounter=0; textCounter=0;
    // meetingIndexCounter=1; // on le remet quand on fetch
}

function fillMeetingBlock(blockId, mtg) {
    const form = document.getElementById(`meetingForm-${blockId}`);
    if (!form) return;

    if (mtg.meeting_date) form.querySelector('input[name="meeting"]').value = mtg.meeting_date;
    if (mtg.end_date) form.querySelector('input[name="end_meeting"]').value = mtg.end_date;

    if (Array.isArray(mtg.qcm)) {
        mtg.qcm.forEach(qc=>{
            if (qc.title==='Lieu') {
                const rad = form.querySelector(`input[name="Lieu"][value="${qc.other_choice}"]`);
                if (rad) rad.checked = true;
            } else {
                createQcmFormBlock(form, qc.title, qc.choices, qc.other_choice);
            }
        });
    }
    if (Array.isArray(mtg.texts)) {
        mtg.texts.forEach(tx=>{
            if (tx.title.startsWith('Commentaire')) {
                createCommentFormBlock(form, tx.title, tx.response);
            } else if (tx.title.startsWith('Question')) {
                createQrFormBlock(form, tx.title, tx.response);
            } else {
                createTextFormBlock(form, tx.title, tx.response);
            }
        });
    }
}

function bindBilanSave() {
    const btn = document.querySelector('.validate-bilan-btn');
    if (!btn) return;
    btn.addEventListener('click', ()=>{
        const form = document.getElementById('formContainer-bilan');
        if (!form) return;
        const fd = new FormData(form);
        fd.append('action','save_bilan');
        fetch('Professor.php',{
            method:'POST',
            body: fd,
            headers:{'X-Requested-With':'XMLHttpRequest'}
        })
            .then(r=>r.json())
            .then(data=>{
                if (data.status==='success') {
                    alert("Bilan sauvegardé!");
                    loadExistingMeetings();
                } else {
                    alert(data.message||"Erreur bilan");
                }
            })
            .catch(err=>{
                console.error(err);
                alert("Erreur lors de la sauvegarde du bilan");
            });
    });
}

function removeDefaultOption(sel) {
    const def = sel.querySelector('option[value=""]');
    if (!def) return;
    if (sel.value!=="") {
        def.style.display="none";
    } else {
        def.style.display="block";
    }
}