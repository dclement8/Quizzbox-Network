var htmlEntities = function (str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
};

var isNumeric = function (n) {
    return !isNaN(parseFloat(n)) && isFinite(n);
};

var storageAvailable = function (type) {
    try {
        var storage = window[type],
            x = '__storage_test__';
        storage.setItem(x, x);
        storage.removeItem(x);
        return true;
    }
    catch (e) {
        return false;
    }
};

var quizz = (function () {
    // Functions nécessaires à la création/modification d'un quizz

    var $quizz = document.querySelector("#questions");
    var Quizzmsg = document.querySelector("#Quizzmsg");
    var json = {
        "quizz": {
            "nom": "Quizz",
            "id_categorie": 0,
            "id": 0,
            "tokenWeb": "0",
            "questions": [
                {
                    "id": 0,
                    "enonce": "",
                    "coefficient": 1,
                    "reponses": [
                        { "id": 0, "nom": "", "estSolution": 0 }
                    ]
                }
            ]
        }
    };

    /* pour les réponses : 0 = faux, 1 = vrai
        Forme du json :
        {
            "quizz": {
                "nom": "Bob",
                "id_categorie": 1,
                "id": 2,
                "tokenWeb": "abc",
				"questions": [
	                {
	                    "id": 1,
	                    "enonce": "Dans quelle ville se situe la plus grande cathédrale de France ?",
	                    "coefficient": 1,
	                    "reponses": [
	                        {"id": 1, "nom": "Rouen", "estSolution": 0},
	                        {"id": 2, "nom": "Amiens", "estSolution": 1},
	                        {"id": 3, "nom": "Strasbourg", "estSolution": 0}
	                    ]
	                },
	                {
	                    "id": 2,
	                    "enonce": "Quelle a été la première ville française libérée lors de la Seconde Guerre mondiale ?",
	                    "coefficient": 1,
	                    "reponses": [
	                        {"id": 4, "nom": "Bayeux", "estSolution": 0},
	                        {"id": 5, "nom": "Caen", "estSolution": 0},
	                        {"id": 6, "nom": "Marseille", "estSolution": 0},
	                        {"id": 7, "nom": "Ajaccio", "estSolution": 1}
	                    ]
	                }
	            ]
            }
        }
    */

    return {
        getJSON: function () {
            return json;
        },

        ajouterQuestion: function () {
            json.quizz.questions.push({ "id": 0, "enonce": "", "coefficient": 1, "reponses": [{ "id": 0, "nom": "", "estSolution": false }] });
            quizz.updateStorage();
            quizz.generer();
        },

        ajouterReponse: function (question) {
            json.quizz.questions[question - 1].reponses.push({ "id": 0, "nom": "", "estSolution": false });
            quizz.updateStorage();
            quizz.generer();
        },

        supprimerQuestion: function (question) {
            json.quizz.questions.splice(question - 1, 1);
            quizz.updateStorage();
            quizz.generer();
        },

        supprimerReponse: function (question, reponse) {
            json.quizz.questions[question - 1].reponses.splice(reponse - 1, 1);
            quizz.updateStorage();
            quizz.generer();
        },

        updateNom: function (value) {
            json.quizz.nom = value;
        },

        updateCategorie: function (value) {
            json.quizz.id_categorie = value;
        },

        updateEnonce: function (question, value) {
            json.quizz.questions[question - 1].enonce = value;
        },

        updateCoefficient: function (question, value) {
            value = parseInt(value);
            if (isNumeric(value)) {
                json.quizz.questions[question - 1].coefficient = value;
            }
        },

        updateReponse: function (question, reponse, value) {
            json.quizz.questions[question - 1].reponses[reponse - 1].nom = value;
        },

        updateSolution: function (question, reponse) {
            json.quizz.questions[question - 1].reponses[reponse - 1].estSolution = !(json.quizz.questions[question - 1].reponses[reponse - 1].estSolution);
            quizz.updateStorage();
        },

        verifierContenu: function () {
            Quizzmsg.innerHTML = '';
            var k;
            for (var i = 0; i < json.quizz.questions.length; i++) {
                if (json.quizz.questions[i].enonce == '' || json.quizz.questions[i].enonce === undefined) {
                    Quizzmsg.innerHTML += 'L\'énoncé de la question ' + (i + 1) + ' est vide.<br />';
                    continue;
                }
                if (json.quizz.questions[i].reponses.length === undefined) {
                    Quizzmsg.innerHTML += 'La question ' + (i + 1) + ' doit comporter des réponses.<br />';
                    continue;
                }
                k = false;
                for (var j = 0; j < json.quizz.questions[i].reponses.length; j++) {
                    if (json.quizz.questions[i].reponses[j].nom == '' || json.quizz.questions[i].reponses[j].nom === undefined) {
                        Quizzmsg.innerHTML += 'La réponse ' + (j + 1) + ' de la question ' + (i + 1) + ' est vide.<br />';
                        continue;
                    }
                    if (json.quizz.questions[i].reponses[j].estSolution == 1) {
                        k = true;
                    }
                }
                if (json.quizz.questions[i].reponses.length < 2) {
                    Quizzmsg.innerHTML += 'La question ' + (i + 1) + ' doit comporter au moins 2 réponses.<br />';
                    continue;
                }
                if (k === false) {
                    Quizzmsg.innerHTML += 'La question ' + (i + 1) + ' doit comporter au moins une réponse juste.<br />';
                    continue;
                }
            }
            if (Quizzmsg.innerHTML == '') {
                return true;
            }
            return false;
        },

        updateStorage: function () {
            if (storageAvailable('localStorage')) {
                console.log("update storage");
                localStorage.setItem("quizzboxEdition", JSON.stringify(json));
            }
        },

        generer: function (data = null) {
            // Génère le formulaire de questions/réponses à partir du JSON
            var tab = '';

            if (data !== null) {
                console.log(data);
                json = data;
            }

            console.log("Génération en cours");

            document.querySelector("#nom").value = json.quizz.nom;
            document.querySelector("#categorie").value = json.quizz.id_categorie;

            for (var i = 1; i <= json.quizz.questions.length; i++) {
                tab += '<table id="question_' + i + '">';
                tab += '<tr><td>Question n°' + i + ' : <input type="text" onchange="quizz.updateEnonce(' + i + ', this.value)" onblur="quizz.updateStorage()" onkeyup="quizz.updateEnonce(' + i + ', this.value)" placeholder="Votre question ?" value="' + json.quizz.questions[i - 1].enonce + '" /></td>';
                tab += '<td>coefficient <input type="number" min="1" max="5" onblur="quizz.updateStorage()" onchange="quizz.updateCoefficient(' + i + ', this.value)" onkeyup="quizz.updateCoefficient(' + i + ', this.value)" value="' + json.quizz.questions[i - 1].coefficient + '" /></td></tr>';

                // Réponses
                for (var j = 1; j <= json.quizz.questions[i - 1].reponses.length; j++) {
                    tab += '<tr id="reponse_' + i + '_' + j + '"><td>Réponse n°' + j + ' : <input type="text" placeholder="Une réponse" onblur="quizz.updateStorage()" onchange="quizz.updateReponse(' + i + ', ' + j + ', this.value)" onkeyup="quizz.updateReponse(' + i + ', ' + j + ', this.value)" value="' + json.quizz.questions[i - 1].reponses[j - 1].nom + '" /></td>';
                    tab += '<td>est solution ? <input type="checkbox" onclick="quizz.updateSolution(' + i + ', ' + j + ')"';
                    if (json.quizz.questions[i - 1].reponses[j - 1].estSolution) tab += ' checked';
                    tab += ' />';
                    if (j > 1) tab += '<input type="button" value="X" onclick="quizz.supprimerReponse(' + i + ', ' + j + ')">';
                    tab += '</td></tr>';
                }

                tab += '<tr class="button"><td><input type="button" class="btn" value="Ajouter une réponse" onclick="quizz.ajouterReponse(' + i + ')" /> </td>';
                tab += '<tr class="button"><td><input type="button" class="btn" value="Ajouter une question" onclick="quizz.ajouterQuestion()" /> <input type="button" class="btn" value="Créez le quizz" onclick="quizz.envoyer()" /></td>';

                if (i > 1) tab += '<td><input type="button" class="btn" value="Supprimer la question" onclick="quizz.supprimerQuestion(' + i + ')"></td>';
                tab += '</tr>';
                tab += '</table>';
            }

            $quizz.innerHTML = tab;
            console.log(json);
        },

        envoyer: function () {
            if ((json.quizz.questions[0].enonce == '' || json.quizz.questions[0].enonce === undefined) ||
                (json.quizz.questions[0].reponses[0] == '' || json.quizz.questions[0].reponses[0] === undefined) ||
                (json.quizz.questions[0].reponses[1] == '' || json.quizz.questions[0].reponses[1] === undefined)) {
                alert('Votre quizz doit comporter au moins 1 question et 2 réponses');
            }
            else if (json.quizz.id_categorie == '' || json.quizz.id_categorie == 0 || json.quizz.id_categorie === undefined) {
                alert('Veuillez choisir une catégorie !');
            }
            else if (!(quizz.verifierContenu())) {
                alert('Votre quizz comporte des erreurs !');
            }
            else {
                document.querySelector("#json").value = JSON.stringify(quizz.getJSON());

                if (confirm("Voulez-vous valider les modifications de ce quizz ?")) {
                    document.querySelector("#formulaire").submit();
                }
            }
        }
    }
})();

var getLocal = (function () {
    var local = null;
    return {
        show: function () {
            /* Affichage bouton pour charger ou supprimer au chargement */
            if (storageAvailable('localStorage')) {
                var dlocal = localStorage.getItem("quizzboxEdition");
                if (dlocal !== undefined && dlocal !== null && dlocal != '{}') {
                    local = JSON.parse(dlocal);
                    document.querySelector("#localQuizz").innerHTML = '<b>Charger un quizz en cours d\'édition</b>\
					'+ local.quizz.nom + ' <input type="button" class="btn" value="Charger" onclick="getLocal.load();">\
					<input type="button" class="btn" value="Supprimer le quizz sauvé" onclick="getLocal.delete();">';
                }
            }
        },

        load: function () {
            quizz.generer(local);
            document.querySelector("#Quizzmsg").innerHTML = 'Quizz chargé !';
        },

        delete: function () {
            localStorage.removeItem("quizzboxEdition");
            document.querySelector("#localQuizz").innerHTML = '';
        }
    }
})();
