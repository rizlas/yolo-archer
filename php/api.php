<?php
session_start();

function get_esisteutente($email)
{
	$servername = "fdb13.atspace.me";
	$username = "1762595_maindb";
	$password = "Ciao1234";
	$dbname = "1762595_maindb";

	$conn = new mysqli($servername, $username, $password, $dbname);
	if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
	$query = "SELECT * FROM UTENTE WHERE UTENTE.email = '$email'";
	$result = $conn->query($query);
	$esiste = false;
	if ($result->num_rows > 0)
	{
		$esiste = true;
	}
	$conn->close();
	return $esiste;
}

function get_punti_utente($email)
{
	$servername = "fdb13.atspace.me";
	$username = "1762595_maindb";
	$password = "Ciao1234";
	$dbname = "1762595_maindb";

	$conn = new mysqli($servername, $username, $password, $dbname);
	if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
	$query = "SELECT punti FROM UTENTE WHERE UTENTE.email = '$email'";
	$result = $conn->query($query);
	$punti = '';
	if ($result->num_rows > 0)
	{
		$row = $result->fetch_assoc();
		$punti = $row["punti"];
	}
	$conn->close();
	return $punti;
}

function get_edifici()
{
	$servername = "fdb13.atspace.me";
	$username = "1762595_maindb";
	$password = "Ciao1234";
	$dbname = "1762595_maindb";

	$conn = new mysqli($servername, $username, $password, $dbname);
	if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
	$query = "SELECT nome_corto FROM EDIFICIO";
	$edifici = array();
	$result = $conn->query($query);
	$esiste = false;
	if ($result->num_rows > 0)
	{
		while($row = $result->fetch_assoc())
		{
			$edifici[] = $row["nome_corto"];
		}
	}
	$conn->close();
	return $edifici;
}

//Si prenota in quella giornata
function prenota()
{
	$email = $_POST["email"];
	$persone = $_POST["persone"];
	$nome_stanza = $_POST["stanza"];
	$edificio = $_POST["edificio"];
	$id_edificio = get_idedificio($edificio);
	$inizio = $_POST["inizio"];
	$fine = $_POST["fine"];

	$query =  "INSERT INTO PRENOTAZIONE VALUES($id_edificio, '$nome_stanza', '$email', $inizio, $fine, $persone)";
	//echo $query;
	exec_non_query($query);
}

class Stanza_prenotata
{
	public $nome = '';
	public $edificio = '';
	public $capienza = 0;
	public $persone = 0;
}


function get_stanze_prenotabili()
{
	$edificio = $_POST["edificio"];
	$id_edificio = get_idedificio($edificio);
	$inizio = $_POST["inizio"];
	$fine = $_POST["fine"];

	$stanze_prentotate = array();

	$servername = "fdb13.atspace.me";
	$username = "1762595_maindb";
	$password = "Ciao1234";
	$dbname = "1762595_maindb";

	$conn = new mysqli($servername, $username, $password, $dbname);
	if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

	$query =
	"
		SELECT  STANZA.nome AS nome, EDIFICIO.nome_corto AS edificio, STANZA.capienza AS capienza, SUM(PRENOTAZIONE.persone) AS persone_prenotate
		FROM PRENOTAZIONE, STANZA, EDIFICIO
		WHERE
			PRENOTAZIONE.nome_stanza = STANZA.nome AND
			EDIFICIO.id_edificio = STANZA.id_edificio AND
			(PRENOTAZIONE.inizio <= $inizio AND PRENOTAZIONE.fine >= $fine) AND
			PRENOTAZIONE.nome_stanza IN
			(
				SELECT STANZA.nome AS nome
				FROM EVENTO,EDIFICIO,STANZA
				WHERE
					EDIFICIO.id_edificio = EVENTO.id_edificio AND
					STANZA.id_edificio = EVENTO.id_edificio AND
					STANZA.nome = EVENTO.nome_stanza AND
					(inizio > $inizio AND fine > $fine OR inizio < $inizio AND fine < $fine) AND
					EDIFICIO.nome_corto LIKE '$edificio' AND
					STANZA.nome NOT IN
					(
						SELECT STANZA.nome AS nome
						FROM EVENTO,EDIFICIO,STANZA
						WHERE
							EDIFICIO.id_edificio = EVENTO.id_edificio AND
							STANZA.id_edificio = EVENTO.id_edificio AND
							STANZA.nome = EVENTO.nome_stanza AND
							(inizio <= $inizio AND fine >= $fine) AND
							EDIFICIO.nome_corto LIKE '$edificio'
						GROUP BY STANZA.nome
					)
				GROUP BY STANZA.nome, EDIFICIO.nome_corto, STANZA.capienza, EDIFICIO.id_edificio
			)
		GROUP BY STANZA.nome, EDIFICIO.nome_corto, STANZA.capienza
	";

	$result = $conn->query($query);
	if ($result->num_rows > 0)
	{
		while($row = $result->fetch_assoc())
		{
			$s = new Stanza_prenotata();
			$s->nome = $row["nome"];
			$s->edificio = $row["edificio"];
			$s->capienza = $row["capienza"];
			$s->persone = $row["persone_prenotate"];

			//echo $s->edificio." ".$s->nome." ".$s->capienza." ".$s->persone.":<br />";
			$stanze_prentotate[] = $s;
		}
	}
	$conn->close();

	return $stanze_prentotate;
}



//TESTATEEEEEEEE-----------------------------------------------------------------------------
function set_nomeutente()
{
	$_SESSION["email"] = $_POST['email'];
}
function get_nomeutente()
{
	return $_SESSION["email"];
}




function exec_non_query($query)
{
	$servername = "fdb13.atspace.me";
	$username = "1762595_maindb";
	$password = "Ciao1234";
	$dbname = "1762595_maindb";

	$conn = new mysqli($servername, $username, $password, $dbname);
	if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
	$conn->query($query);
	$conn->close();
}
function get_idedificio($nome_corto_edificio)
{
	$servername = "fdb13.atspace.me";
	$username = "1762595_maindb";
	$password = "Ciao1234";
	$dbname = "1762595_maindb";

	$conn = new mysqli($servername, $username, $password, $dbname);
	if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
	$query = "SELECT id_edificio FROM EDIFICIO WHERE EDIFICIO.nome_corto = '$nome_corto_edificio'";
	$result = $conn->query($query);
	$idedificio = '';
	if ($result->num_rows > 0)
	{
		$row = $result->fetch_assoc();
		$idedificio = $row["id_edificio"];
	}
	$conn->close();
	return $idedificio;
}

function fai_commento()
{
	$testo = $_POST["testo"];
	$email = $_POST["email"];
	$persone = $_POST["persone"];
	$nome_stanza = $_POST["stanza"];
	$edificio = $_POST["edificio"];
	$id_edificio = get_idedificio($edificio);
	$timestamp = time();

	$query =  "INSERT INTO COMMENTO VALUES($id_edificio, '$nome_stanza', '$email', $timestamp, '$testo', $persone)";
	exec_non_query($query);
}
function crea_utente()
{
	$email = $_POST["email"];
	$password = $_POST["password"];

	$query =  "INSERT INTO UTENTE VALUES('$email', '$password', 25)";
	//echo $query;
	exec_non_query($query);
}
function Im_like_and_i_know_it()
{
	$edificio = $_POST["edificio"];
	$id_edificio = get_idedificio($edificio);
	$email = $_POST["email_votante"];
	$email_commento = $_POST["email_commento"];
	$nomestanza = $_POST["stanza"];
	$timestamp = $_POST["timestamp"];

	$query =  "INSERT INTO VOTO VALUES($id_edificio, '$nomestanza', '$email_commento', $timestamp, '$email', 1)";
	//echo $query;
	exec_non_query($query);
}
function Im_dislike_and_i_know_it()
{
	$edificio = $_POST["edificio"];
	$id_edificio = get_idedificio($edificio);
	$email = $_POST["email_votante"];
	$email_commento = $_POST["email_commento"];
	$nomestanza = $_POST["stanza"];
	$timestamp = $_POST["timestamp"];

	$query =  "INSERT INTO VOTO VALUES($id_edificio, '$nomestanza', '$email_commento', $timestamp, '$email', -1)";
	//echo $query;
	exec_non_query($query);
}

class Stanza
{
	public $nome = '';
	public $edificio = '';
	public $capienza = 0;
	public $commenti = array();
}
class Commento
{
	public $email = '';
	public $timestamp = 0;
	public $testo = '';
	public $likes = 0;
	public $dislike = 0;
	public $quante_persone = 0;
}
function get_stanze_libere_adesso_ma_devo_aggiungere_i_likes($edificio, $adesso)
{
	$stanze = array();

	$servername = "fdb13.atspace.me";
	$username = "1762595_maindb";
	$password = "Ciao1234";
	$dbname = "1762595_maindb";

	$conn = new mysqli($servername, $username, $password, $dbname);
	if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

	$query =
	"
		SELECT STANZA.nome AS nome, EDIFICIO.nome_corto AS edificio, STANZA.capienza AS capienza
		FROM EVENTO,EDIFICIO,STANZA
		WHERE
			EDIFICIO.id_edificio = EVENTO.id_edificio AND
			STANZA.id_edificio = EVENTO.id_edificio AND
			STANZA.nome = EVENTO.nome_stanza AND
			(inizio > $adesso AND fine > $adesso OR inizio < $adesso AND fine < $adesso) AND
			EDIFICIO.nome_corto LIKE '$edificio' AND
			STANZA.nome NOT IN
			(
				SELECT STANZA.nome AS nome
				FROM EVENTO,EDIFICIO,STANZA
				WHERE
					EDIFICIO.id_edificio = EVENTO.id_edificio AND
					STANZA.id_edificio = EVENTO.id_edificio AND
					STANZA.nome = EVENTO.nome_stanza AND
					(inizio <= $adesso AND fine >= $adesso) AND
					EDIFICIO.nome_corto LIKE '$edificio'
				GROUP BY STANZA.nome
			)
		GROUP BY STANZA.nome, EDIFICIO.nome_corto, STANZA.capienza, EDIFICIO.id_edificio
	";

	$result = $conn->query($query);
	if ($result->num_rows > 0)
	{
		while($row = $result->fetch_assoc())
		{
			$s = new Stanza();
			$s->nome = $row["nome"];
			$s->edificio = $row["edificio"];
			$s->capienza = $row["capienza"];
			$stanze[] = $s;
		}
	}
	$conn->close();

	return $stanze;
}
function get_stanze_adesso()
{
	date_default_timezone_set('Europe/Rome');
	$edificio = $_POST["edificio"];
	$adesso = 1418117400;//time();

	$stanze = get_stanze_libere_adesso_ma_devo_aggiungere_i_likes($edificio, $adesso);


	$servername = "fdb13.atspace.me";
	$username = "1762595_maindb";
	$password = "Ciao1234";
	$dbname = "1762595_maindb";

	$conn = new mysqli($servername, $username, $password, $dbname);
	if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
	//aggiungo i commenti
	foreach ($stanze as &$s)
	{


		$edif = get_idedificio($s->edificio);

		$nomes = $s->nome;
		$query =
		"SELECT email_utente AS email, VOTO.time_unix AS tempo, testo AS commento, persone AS quante_persone, SUM(CASE WHEN valore = 1 THEN 1 ELSE 0 END) AS likes, SUM(CASE WHEN valore = -1 THEN 1 ELSE 0 END) AS dislikes
		FROM COMMENTO, VOTO
		WHERE
			COMMENTO.id_edificio = $edif AND
			COMMENTO.nome_stanza = '$nomes' AND
			VOTO.id_edificio    = COMMENTO.id_edificio AND
			VOTO.nome_stanza    = COMMENTO.nome_stanza AND
			VOTO.email_commento = COMMENTO.email_utente AND
			VOTO.time_unix      = COMMENTO.time_unix
		GROUP BY email_utente, VOTO.time_unix, testo, persone";

		//echo $query."<br />";

		$result = $conn->query($query);
		if ($result->num_rows > 0)
		{
			while($row = $result->fetch_assoc())
			{
				$c = new Commento();
				$c->email = $row["email"];
				$c->timestamp = $row["tempo"];
				$c->testo = $row["commento"];
				$c->likes = $row["likes"];
				$c->dislike = $row["dislikes"];
				$c->quante_persone = $row["quante_persone"];

				$s->commenti[] = $c;
			}
		}
	}
	$conn->close();


	return $stanze;
}



?>
