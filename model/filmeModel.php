<?php

require_once 'config.php';

function importarFilmes(string $api_key)
{
    $url = "https://api.themoviedb.org/3/movie/popular?api_key=$api_key&language=pt-BR&page=1";
    $response = file_get_contents($url);
    $data = json_decode($response, true); // Passa a resposta como array associativo, os índices serão strings que correspondem aos nomes dos campos do JSON (ex: 'title', 'overview', 'poster_path')

    foreach ($data['results'] as $filme) {
        $titulo = $filme['title']; // o title é como a API retorna o título do filme
        $descricao = $filme['overview']; // o overview é como a API retorna a descrição do filme
        $poster = "https://image.tmdb.org/t/p/w500" . $filme['poster_path']; // o poster_path é como a API retorna o caminho do poster do filme, concatenado com a URL base para formar o link completo da imagem 

        // Insere os dados no banco de dados
        salvarFilme($titulo, $descricao, $poster);

        global $pdo;

        try {
            $titulo = $pdo->quote($titulo);
            $descricao = $pdo->quote($descricao);
            $poster = $pdo->quote($poster);

            $verificacao = "SELECT * FROM filmes WHERE titulo = $titulo";

            $resultado = $pdo->query($verificacao);

            if ($resultado->rowCount() > 0) {
                return; // Filme já existe, não insere novamente
            }

            $sql = "INSERT INTO filmes (titulo, descricao, poster) VALUES ($titulo, $descricao, $poster)";
            $pdo->query($sql);
        } catch (PDOException $e) {
            echo "Erro ao verificar filme: " . $e->getMessage();
        }
    }

    function listarFilmes()
    {
        global $pdo;

        try {
            $sql = "SELECT * FROM filmes";
            $resultado = $pdo->query($sql);

            return $resultado->fetchAll(PDO::FETCH_ASSOC); // fetchAll retorna todos (all) os resultados como um array associativo, e o PDO::FETCH_ASSOC garante que os índices do array sejam os nomes das colunas da tabela (ex: 'titulo', 'descricao', 'poster')
        } catch (PDOException $e) {
            echo "Erro ao listar filmes: " . $e->getMessage();
        }
    }
}
?>