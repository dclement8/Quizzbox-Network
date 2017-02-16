var creer = (function() {
    // Functions nécessaires à la création d'un quizz

    var $quizz = document.querySelector("#questions");
    var json = {
        "quizz": {
            "nom": "Quizz",
            "id_categorie": 0,
            "id": 0,
            "tokenWeb": "0"
        },
        "questions": [
            {
                "id": 0,
                "enonce": "",
                "coefficient": 1,
                "reponses": [
                    {"id": 0, "nom": "", "estSolution": 0}
                ]
            }
        ]
    };

    /* pour les réponses : 0 = faux, 1 = vrai
        Forme du json :
        {
            "quizz": {
                "nom": "Bob",
                "id_categorie": 1,
                "id": 2,
                "tokenWeb": "abc"
            },
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
    */

    return {
        getJSON: function() {
            return json;
        },

        ajouterQuestion: function() {
            json.questions.push({"id":0,"enonce":"","coefficient":1,"reponses":[{"id":0,"nom": "", "estSolution": false}]});
            creer.generer();
        },

        ajouterReponse: function(question) {
            json.questions[question-1].reponses.push({"id":0,"nom":"","estSolution":false});
            creer.generer();
        },

        supprimerQuestion: function(question) {
            json.questions.splice(question-1, 1);
            creer.generer();
        },

        supprimerReponse: function(question, reponse) {
            json.questions[question-1].reponses.splice(reponse-1, 1);
            creer.generer();
        },

        updateNom: function(value) {
            json.quizz.nom = value;
        },

        updateCategorie: function(value) {
            json.quizz.id_categorie = value;
        },

        updateEnonce: function(question, value) {
            json.questions[question-1].enonce = value;
        },

        updateCoefficient: function(question, value) {
            json.questions[question-1].coefficient = value;
        },

        updateReponse: function(question, reponse, value) {
            json.questions[question-1].reponses[reponse-1].nom = value;
        },

        updateSolution: function(question, reponse) {
            json.questions[question-1].reponses[reponse-1].estSolution = !(json.questions[question-1].reponses[reponse-1].estSolution);
        },

        generer: function() {
            // Génère le formulaire de questions/réponses à partir du JSON
            var tab = '';

            console.log("Génération en cours");

            for(var i=1; i <= json.questions.length; i++) {
                tab += '<table id="question_'+i+'">';
                tab += '<tr><td>Question n°'+i+' : <input type="text" onkeyup="creer.updateEnonce('+i+', this.value)" value="'+json.questions[i - 1].enonce+'" /></td>';
                tab += '<td>coefficient <input type="number" value="1" min="1" max="5" onkeyup="creer.updateCoefficient('+i+', this.value)" value="'+json.questions[i - 1].coefficient+'" /></td></tr>';

                // Réponses
                for(var j=1; j <= json.questions[i - 1].reponses.length; j++) {
                    tab += '<tr id="reponse_'+i+'_'+j+'"><td>Réponse n°'+j+' : <input type="text" onkeyup="creer.updateReponse('+i+', '+j+', this.value)" value="'+json.questions[i - 1].reponses[j - 1].nom+'" /></td>';
                    tab += '<td>est solution ? <input type="checkbox" onclick="creer.updateSolution('+i+', '+j+')"';
                    if(json.questions[i-1].reponses[j-1].estSolution) tab += ' checked';
                    tab += ' />';
                    if(j > 1) tab += '<input type="button" value="X" onclick="creer.supprimerReponse('+i+', '+j+')">';
                    tab += '</td></tr>';
                }

                tab += '<tr class="button"><td><input type="button" value="Ajouter une réponse" onclick="creer.ajouterReponse('+i+')" /> ';
                if(i > 1) tab += '<input type="button" value="X" onclick="creer.supprimerQuestion('+i+')">';
                tab += '</td><td></td></tr>';
                tab += '</table>';
            }

            $quizz.innerHTML = tab;
            console.log(json);
        },

        envoyer: function() {
            document.querySelector("#json").value = creer.getJSON();

            if(confirm("Voulez-vous créer ce quizz ?")) {
                document.querySelector("#formulaire").submit();
            }
        }
    }
}) ();

//creer.generer();
