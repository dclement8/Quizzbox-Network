var creer = (function() {
    // Functions nécessaires à la création d'un quizz

    var $quizz = document.querySelector("#questions");
    var json = {
        "questions":[
            {
                "enonce": "",
                "coefficient": 1,
                "reponses": [
                    {"nom": "", "estSolution": false}
                ]
            }
        ]
    };

    /*
        Forme du json :
        {
            "questions": [
                {
                    "enonce": "Dans quelle ville se situe la plus grande cathédrale de France ?",
                    "coefficient": 1,
                    "reponses": [
                        {"nom": "Rouen", "estSolution": false},
                        {"nom": "Amiens", "estSolution": true},
                        {"nom": "Strasbourg", "estSolution": false}
                    ]
                },
                {
                    "enonce": "Quelle a été la première ville française libérée lors de la Seconde Guerre mondiale ?",
                    "coefficient": 1,
                    "reponses": [
                        {"nom": "Bayeux", "estSolution": false},
                        {"nom": "Caen", "estSolution": false},
                        {"nom": "Marseille", "estSolution": false},
                        {"nom": "Ajaccio", "estSolution": true}
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
            json.questions.push({"enonce":"","coefficient":1,"reponses":[{"nom": "", "estSolution": false}]});
            creer.generer();
        },

        ajouterReponse: function(question) {
            json.questions[question-1].reponses.push({"nom":"","estSolution":false});
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
                tab += '<td>coefficient <input type="number" onkeyup="creer.updateCoefficient('+i+', this.value)" value="'+json.questions[i - 1].coefficient+'" /></td></tr>';

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
        },

        envoyer: function() {
            document.querySelector("#json").value = creer.getJSON();

            if(confirm("Voulez-vous créer ce quizz ?")) {
                document.querySelector("#formulaire").submit();
            }
        }
    }
}) ();

creer.generer();
