<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style/index.css">
    <link rel="stylesheet" href="../style/profil_admin.css">
    <title>Profil Admin - L’oro di Cicerone</title>
</head>
<body>
    <header>
        <a href="index.html"><h1>L’oro di Cicerone</h1></a>
        <nav>
            <ul>
                <li><a href="index.html">Accueil</a></li>
            </ul>
        </nav>
    </header>
    <h1>Page d'administration</h1>
    <div class="grid">
        <section class="graphique">
            <h2>Graphique</h2>
            <p>In progress, Brick by Boring Brick. Awaiting the JS...</p>
        </section>
        <section class="table-utilisateur">
            <h2>Utilisateurs</h2>
            <input type="text" name="search" placeholder="Saisir un Identifiant">
            <input type="submit" id="search-button">
            <table>
                <tr>
                    <td>Selection</td>
                    <th>Nom d'utilisateur</th>
                    <th>Identifiant</th>
                    <th>Statut</th>
                    <th>Dernière date de connexion</th>
                    <th>Bloquer</th>
                </tr>
                <tr>
                    <td class="check" ><input type="checkbox" id="myCheckbox"><label for="myCheckbox"></label></td>
                    <td><a href="profil_client.html">admin1</a></td>
                    <td>#0000</td>
                    <td><select name="role">
                        <option value="role">Utilisateur</option>
                        <option value="role">Livreur</option>
                        <option value="role">Restaurateur</option>
                        <option value="role">Administrateur</option>
                    </select></td>
                    <td>2025-01-01</td>
                    <td><button class="bloquer">Bloquer</button></td>
                </tr>
                <tr>
                    <td class="check"><input type="checkbox" id="myCheckbox2"><label for="myCheckbox2"></label></td>
                    <td><a href="profil_client.html">admin2</a></td>
                    <td>#0001</td>
                    <td><select name="role">
                        <option value="role">Utilisateur</option>
                        <option value="role">Livreur</option>
                        <option value="role">Restaurateur</option>
                        <option value="role">Administrateur</option>
                    </select></td>
                    <td>1s</td>
                    <td><button class="bloquer">Bloquer</button></td>
                </tr>
            </table>
        </section>
    </div>
</body>
</html>