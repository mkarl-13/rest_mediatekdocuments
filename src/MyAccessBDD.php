<?php
include_once("AccessBDD.php");

/**
 * Classe de construction des requêtes SQL
 * hérite de AccessBDD qui contient les requêtes de base
 * Pour ajouter une requête :
 * - créer la fonction qui crée une requête (prendre modèle sur les fonctions 
 *   existantes qui ne commencent pas par 'traitement')
 * - ajouter un 'case' dans un des switch des fonctions redéfinies 
 * - appeler la nouvelle fonction dans ce 'case'
 */
class MyAccessBDD extends AccessBDD {
	    
    /**
     * constructeur qui appelle celui de la classe mère
     */
    public function __construct(){
        try{
            parent::__construct();
        }catch(\Exception $e){
            throw $e;
        }
    }

    /**
     * demande de recherche
     * @param string $table
     * @param array|null $champs nom et valeur de chaque champ
     * @return array|null tuples du résultat de la requête ou null si erreur
     * @override
     */	
    protected function traitementSelect(string $table, ?array $champs) : ?array{
        switch($table){  
            case "livre" :
                return $this->selectAllLivres();
            case "dvd" :
                return $this->selectAllDvd();
            case "revue" :
                return $this->selectAllRevues();
            case "exemplaire" :
                return $this->selectExemplairesRevue($champs);
            case "genre" :
            case "public" :
            case "rayon" :
            case "etat" :
                // select portant sur une table contenant juste id et libelle
                return $this->selectTableSimple($table);
            case "nextidlivre" :
                return $this->selectNextIdLivre();
            case "nextiddvd" :
                return $this->selectNextIdDvd();
            case "nextidrevue" :
                return $this->selectNextIdRevue();
            case "" :
                // return $this->uneFonction(parametres);
            default:
                // cas général
                return $this->selectTuplesOneTable($table, $champs);
        }	
    }

    /**
     * demande d'ajout (insert)
     * @param string $table
     * @param array|null $champs nom et valeur de chaque champ
     * @return int|null nombre de tuples ajoutés ou null si erreur
     * @override
     */	
    protected function traitementInsert(string $table, ?array $champs) : ?int{
        switch($table){
            case "insertlivre" :
                return $this->insertLivre($champs);
            case "insertdvd" :
                return $this->insertDvd($champs);
            case "insertrevue" :
                return $this->insertRevue($champs);
            case "" :
                // return $this->uneFonction(parametres);
            default:                    
                // cas général
                return $this->insertOneTupleOneTable($table, $champs);	
        }
    }
    
    /**
     * demande de modification (update)
     * @param string $table
     * @param string|null $id
     * @param array|null $champs nom et valeur de chaque champ
     * @return int|null nombre de tuples modifiés ou null si erreur
     * @override
     */	
    protected function traitementUpdate(string $table, ?string $id, ?array $champs) : ?int{
        switch($table){
            case "updatelivre" :
                return $this->updateLivre($champs);
            case "updatedvd" :
                return $this->updateDvd($champs);
            case "updaterevue" :
                return $this->updateRevue($champs);
            case "" :
                // return $this->uneFonction(parametres);
            default:                    
                // cas général
                return $this->updateOneTupleOneTable($table, $id, $champs);
        }	
    }  
    
    /**
     * demande de suppression (delete)
     * @param string $table
     * @param array|null $champs nom et valeur de chaque champ
     * @return int|null nombre de tuples supprimés ou null si erreur
     * @override
     */	
    protected function traitementDelete(string $table, ?array $champs) : ?int{
        switch($table){
            case "deletelivre" :
                return $this->deleteLivre($champs);
            case "deletedvd" :
                return $this->deleteDvd($champs);
            case "deleterevue" :
                return $this->deleteRevue($champs);
            case "" :
                // return $this->uneFonction(parametres);
            default:                    
                return $this->deleteTuplesOneTable($table, $champs);	
        }
    }	    
        
    /**
     * récupère les tuples d'une seule table
     * @param string $table
     * @param array|null $champs
     * @return array|null 
     */
    private function selectTuplesOneTable(string $table, ?array $champs) : ?array{
        if(empty($champs)){
            // tous les tuples d'une table
            $requete = "select * from $table;";
            return $this->conn->queryBDD($requete);  
        }else{
            // tuples spécifiques d'une table
            $requete = "select * from $table where ";
            foreach ($champs as $key => $value){
                $requete .= "$key=:$key and ";
            }
            // (enlève le dernier and)
            $requete = substr($requete, 0, strlen($requete)-5);	          
            return $this->conn->queryBDD($requete, $champs);
        }
    }	

    /**
     * demande d'ajout (insert) d'un tuple dans une table
     * @param string $table
     * @param array|null $champs
     * @return int|null nombre de tuples ajoutés (0 ou 1) ou null si erreur
     */	
    private function insertOneTupleOneTable(string $table, ?array $champs) : ?int{
        if(empty($champs)){
            return null;
        }
        // construction de la requête
        $requete = "insert into $table (";
        foreach ($champs as $key => $value){
            $requete .= "$key,";
        }
        // (enlève la dernière virgule)
        $requete = substr($requete, 0, strlen($requete)-1);
        $requete .= ") values (";
        foreach ($champs as $key => $value){
            $requete .= ":$key,";
        }
        // (enlève la dernière virgule)
        $requete = substr($requete, 0, strlen($requete)-1);
        $requete .= ");";
        return $this->conn->updateBDD($requete, $champs);
    }

    /**
     * demande de modification (update) d'un tuple dans une table
     * @param string $table
     * @param string\null $id
     * @param array|null $champs 
     * @return int|null nombre de tuples modifiés (0 ou 1) ou null si erreur
     */	
    private function updateOneTupleOneTable(string $table, ?string $id, ?array $champs) : ?int {
        if(empty($champs)){
            return null;
        }
        if(is_null($id)){
            return null;
        }
        // construction de la requête
        $requete = "update $table set ";
        foreach ($champs as $key => $value){
            $requete .= "$key=:$key,";
        }
        // (enlève la dernière virgule)
        $requete = substr($requete, 0, strlen($requete)-1);				
        $champs["id"] = $id;
        $requete .= " where id=:id;";		
        return $this->conn->updateBDD($requete, $champs);	        
    }
    
    /**
     * demande de suppression (delete) d'un ou plusieurs tuples dans une table
     * @param string $table
     * @param array|null $champs
     * @return int|null nombre de tuples supprimés ou null si erreur
     */
    private function deleteTuplesOneTable(string $table, ?array $champs) : ?int{
        if(empty($champs)){
            return null;
        }
        // construction de la requête
        $requete = "delete from $table where ";
        foreach ($champs as $key => $value){
            $requete .= "$key=:$key and ";
        }
        // (enlève le dernier and)
        $requete = substr($requete, 0, strlen($requete)-5);   
        return $this->conn->updateBDD($requete, $champs);	        
    }
 
    /**
     * récupère toutes les lignes d'une table simple (qui contient juste id et libelle)
     * @param string $table
     * @return array|null
     */
    private function selectTableSimple(string $table) : ?array{
        $requete = "select * from $table order by libelle;";		
        return $this->conn->queryBDD($requete);	    
    }
    
    /**
     * récupère toutes les lignes de la table Livre et les tables associées
     * @return array|null
     */
    private function selectAllLivres() : ?array{
        $requete = "Select l.id, l.ISBN, l.auteur, d.titre, d.image, l.collection, ";
        $requete .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $requete .= "from livre l join document d on l.id=d.id ";
        $requete .= "join genre g on g.id=d.idGenre ";
        $requete .= "join public p on p.id=d.idPublic ";
        $requete .= "join rayon r on r.id=d.idRayon ";
        $requete .= "order by titre ";		
        return $this->conn->queryBDD($requete);
    }	

    /**
     * récupère toutes les lignes de la table DVD et les tables associées
     * @return array|null
     */
    private function selectAllDvd() : ?array{
        $requete = "Select l.id, l.duree, l.realisateur, d.titre, d.image, l.synopsis, ";
        $requete .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $requete .= "from dvd l join document d on l.id=d.id ";
        $requete .= "join genre g on g.id=d.idGenre ";
        $requete .= "join public p on p.id=d.idPublic ";
        $requete .= "join rayon r on r.id=d.idRayon ";
        $requete .= "order by titre ";	
        return $this->conn->queryBDD($requete);
    }	

    /**
     * récupère toutes les lignes de la table Revue et les tables associées
     * @return array|null
     */
    private function selectAllRevues() : ?array{
        $requete = "Select l.id, l.periodicite as idPeriodicite, pe.libelle as periodicite, d.titre, d.image, l.delaiMiseADispo, ";
        $requete .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $requete .= "from revue l join document d on l.id=d.id ";
        $requete .= "join genre g on g.id=d.idGenre ";
        $requete .= "join public p on p.id=d.idPublic ";
        $requete .= "join rayon r on r.id=d.idRayon ";
        $requete .= "join periodicite pe on pe.id=l.periodicite ";
        $requete .= "order by titre ";
        return $this->conn->queryBDD($requete);
    }

    /**
     * récupère tous les exemplaires d'une revue
     * @param array|null $champs 
     * @return array|null
     */
    private function selectExemplairesRevue(?array $champs) : ?array{
        if(empty($champs)){
            return null;
        }
        if(!array_key_exists('id', $champs)){
            return null;
        }
        $champNecessaire['id'] = $champs['id'];
        $requete = "Select e.id, e.numero, e.dateAchat, e.photo, e.idEtat ";
        $requete .= "from exemplaire e join document d on e.id=d.id ";
        $requete .= "where e.id = :id ";
        $requete .= "order by e.dateAchat DESC";
        return $this->conn->queryBDD($requete, $champNecessaire);
    }		 
    
    /**
     * récupère le dernier id disponible pour les livres
     * @return array|null
     */
    private function selectNextIdLivre(): ?array {
        $requete = "Select lpad(max(cast(id as unsigned)) + 1, 5, '0') as id from livre";
        return $this->conn->queryBDD($requete);
    }

    /**
     * récupère le dernier id disponible pour les dvd
     * @return array|null
     */
    private function selectNextIdDvd(): ?array {
        $requete = "Select max(cast(id as unsigned)) + 1 as id from dvd";
        return $this->conn->queryBDD($requete);
    }

    /**
     * récupère le dernier id disponible pour les revues
     * @return array|null
     */
    private function selectNextIdRevue(): ?array {
        $requete = "Select max(cast(id as unsigned)) + 1 as id from revue";
        return $this->conn->queryBDD($requete);
    }
    
    /**
     * Insère un livre dans les tables document, livres_dvd et livre
     * @param array|null $champs
     * @return int|null 1 si succès, null si erreur
     */
    private function insertLivre(?array $champs): ?int {
        if (empty($champs))
            return null;
        try {
            $this->conn->beginTransaction();

            $this->insertOneTupleOneTable("document", [
                "id" => $champs["id"],
                "titre" => $champs["titre"],
                "image" => $champs["image"],
                "idGenre" => $champs["idGenre"],
                "idPublic" => $champs["idPublic"],
                "idRayon" => $champs["idRayon"]
            ]);

            $this->insertOneTupleOneTable("livres_dvd", [
                "id" => $champs["id"]
            ]);

            $this->insertOneTupleOneTable("livre", [
                "id" => $champs["id"],
                "ISBN" => $champs["ISBN"],
                "auteur" => $champs["auteur"],
                "collection" => $champs["collection"]
            ]);

            $this->conn->commit();
            return 1;
        } catch (\Exception $e) {
            $this->conn->rollBack();
            return null;
        }
    }
    
    /**
     * Insère un dvd dans les tables document, livres_dvd et dvd
     * @param array|null $champs
     * @return int|null 1 si succès, null si erreur
     */
    private function insertDvd(?array $champs): ?int {
        if (empty($champs))
            return null;
        try {
            $this->conn->beginTransaction();

            $this->insertOneTupleOneTable("document", [
                "id" => $champs["id"],
                "titre" => $champs["titre"],
                "image" => $champs["image"],
                "idGenre" => $champs["idGenre"],
                "idPublic" => $champs["idPublic"],
                "idRayon" => $champs["idRayon"]
            ]);

            $this->insertOneTupleOneTable("livres_dvd", [
                "id" => $champs["id"]
            ]);

            $this->insertOneTupleOneTable("dvd", [
                "id" => $champs["id"],
                "synopsis" => $champs["synopsis"],
                "realisateur" => $champs["realisateur"],
                "duree" => $champs["duree"]
            ]);

            $this->conn->commit();
            return 1;
        } catch (\Exception $e) {
            $this->conn->rollBack();
            return null;
        }
    }
    
    /**
     * Insère une revue dans les tables document et revue
     * @param array|null $champs
     * @return int|null 1 si succès, null si erreur
     */
    private function insertRevue(?array $champs): ?int {
        if (empty($champs))
            return null;
        try {
            $this->conn->beginTransaction();

            $this->insertOneTupleOneTable("document", [
                "id" => $champs["id"],
                "titre" => $champs["titre"],
                "image" => $champs["image"],
                "idGenre" => $champs["idGenre"],
                "idPublic" => $champs["idPublic"],
                "idRayon" => $champs["idRayon"]
            ]);

            $this->insertOneTupleOneTable("revue", [
                "id" => $champs["id"],
                "periodicite" => $champs["periodicite"],
                "delaiMiseADispo" => $champs["delaiMiseADispo"]
            ]);

            $this->conn->commit();
            return 1;
        } catch (\Exception $e) {
            $this->conn->rollBack();
            return null;
        }
    }
    
    /**
     * Modifie un livre dans les tables document et livre
     * @param array|null $champs
     * @return int|null 1 si succès, null si erreur
     */
    private function updateLivre(?array $champs): ?int {
        if (empty($champs))
            return null;
        try {
            $this->conn->beginTransaction();

            $this->updateOneTupleOneTable("document", $champs["id"], [
                "titre" => $champs["titre"],
                "image" => $champs["image"],
                "idGenre" => $champs["idGenre"],
                "idPublic" => $champs["idPublic"],
                "idRayon" => $champs["idRayon"]
            ]);

            $this->updateOneTupleOneTable("livre", $champs["id"], [
                "ISBN" => $champs["ISBN"],
                "auteur" => $champs["auteur"],
                "collection" => $champs["collection"]
            ]);

            $this->conn->commit();
            return 1;
        } catch (\Exception $e) {
            $this->conn->rollBack();
            return null;
        }
    }
    
    /**
     * Modifie un dvd dans les tables document et dvd
     * @param array|null $champs
     * @return int|null 1 si succès, null si erreur
     */
    private function updateDvd(?array $champs): ?int {
        if (empty($champs))
            return null;
        try {
            $this->conn->beginTransaction();
            $this->updateOneTupleOneTable("document", $champs["id"], [
                "titre" => $champs["titre"],
                "image" => $champs["image"],
                "idGenre" => $champs["idGenre"],
                "idPublic" => $champs["idPublic"],
                "idRayon" => $champs["idRayon"]
            ]);
            $this->updateOneTupleOneTable("dvd", $champs["id"], [
                "synopsis" => $champs["synopsis"],
                "realisateur" => $champs["realisateur"],
                "duree" => $champs["duree"]
            ]);
            $this->conn->commit();
            return 1;
        } catch (\Exception $e) {
            $this->conn->rollBack();
            return null;
        }
    }
    
    /**
     * Modifie une revue dans les tables document et revue
     * @param array|null $champs
     * @return int|null 1 si succès, null si erreur
     */
    private function updateRevue(?array $champs): ?int {
        if (empty($champs))
            return null;
        try {
            $this->conn->beginTransaction();
            $this->updateOneTupleOneTable("document", $champs["id"], [
                "titre" => $champs["titre"],
                "image" => $champs["image"],
                "idGenre" => $champs["idGenre"],
                "idPublic" => $champs["idPublic"],
                "idRayon" => $champs["idRayon"]
            ]);
            $this->updateOneTupleOneTable("revue", $champs["id"], [
                "periodicite" => $champs["periodicite"],
                "delaiMiseADispo" => $champs["delaiMiseADispo"]
            ]);
            $this->conn->commit();
            return 1;
        } catch (\Exception $e) {
            $this->conn->rollBack();
            return null;
        }
    }
    
    /**
     * Supprime un livre dans les tables livre, livres_dvd et document
     * @param array|null $champs
     * @return int|null 1 si succès, null si erreur
     */
    private function deleteLivre(?array $champs): ?int {
        if (empty($champs))
            return null;
        try {
            $this->conn->beginTransaction();

            $this->deleteTuplesOneTable("livre", ["id" => $champs["id"]]);
            $this->deleteTuplesOneTable("livres_dvd", ["id" => $champs["id"]]);
            $this->deleteTuplesOneTable("document", ["id" => $champs["id"]]);

            $this->conn->commit();
            return 1;
        } catch (\Exception $e) {
            $this->conn->rollBack();
            return null;
        }
    }

    /**
     * Supprime un dvd dans les tables dvd, livres_dvd et document
     * @param array|null $champs
     * @return int|null 1 si succès, null si erreur
     */
    private function deleteDvd(?array $champs): ?int {
        if (empty($champs))
            return null;
        try {
            $this->conn->beginTransaction();

            $this->deleteTuplesOneTable("dvd", ["id" => $champs["id"]]);
            $this->deleteTuplesOneTable("livres_dvd", ["id" => $champs["id"]]);
            $this->deleteTuplesOneTable("document", ["id" => $champs["id"]]);

            $this->conn->commit();
            return 1;
        } catch (\Exception $e) {
            $this->conn->rollBack();
            return null;
        }
    }

    /**
     * Supprime une revue dans les tables revues et document
     * @param array|null $champs
     * @return int|null 1 si succès, null si erreur
     */
    private function deleteRevue(?array $champs): ?int {
        if (empty($champs))
            return null;
        try {
            $this->conn->beginTransaction();

            $this->deleteTuplesOneTable("exemplaire", ["id" => $champs["id"]]);
            $this->deleteTuplesOneTable("revue", ["id" => $champs["id"]]);
            $this->deleteTuplesOneTable("document", ["id" => $champs["id"]]);

            $this->conn->commit();
            return 1;
        } catch (\Exception $e) {
            $this->conn->rollBack();
            return null;
        }
    }
}