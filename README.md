# Diagnóstico Corporativo (teste de motivação)

Projeto de curricularização. Questionário web com 30 perguntas, em que cada pergunta divide 3 pontos entre duas respostas (A e B). Os pontos de cada elemento (Fisiológicas, Segurança, Sociais, Auto-estima e Realização, inspirados na hierarquia de necessidades de Maslow) viram um relatório com tabela, gráfico de colunas e PDF. As respostas são gravadas num banco MySQL, e uma tela de administração mostra as médias e mostra um gráfico da média geral e um gráfico individual de cada respondente, e exporta tudo para Excel.

## Estrutura

Na pasta principal ficam as páginas que o visitante vê:

- `index.html`: estrutura da página do questionário e do resultado.
- `style.css`: visual da página.
- `script.js`: lógica do questionário (perguntas, pontos automáticos, cálculo, gráfico e envio das respostas).
- `grafico.js`: desenha o gráfico de colunas (usado no questionário e no admin).

Na pasta `php/` ficam os arquivos do servidor e do banco:

- `php/salvar.php`: recebe as respostas, valida tudo no servidor e grava no banco.
- `php/conexao.php`: conexão com o banco e lista dos elementos de cada pergunta.
- `php/config.php`: dados do banco e senha do admin (não vai para o GitHub). Use `php/config.example.php` como modelo.
- `php/database.sql`: criação das tabelas `respondentes` e `respostas`.
- `php/admin/index.php`: resultados, médias por setor, gráfico da média geral e de cada respondente, e exportação em CSV (protegido por senha).

## Rodando no seu computador (XAMPP)

1. Ligue o Apache e o MySQL no painel do XAMPP.
2. Copie a pasta `diagnostico` para `C:\xampp\htdocs\`.
3. No phpMyAdmin (`http://localhost/phpmyadmin`), crie o banco `diagnostico`, abra a aba SQL com ele selecionado e execute o conteúdo de `php/database.sql`.
4. Acesse `http://localhost/diagnostico/` para responder e `http://localhost/diagnostico/php/admin/` para ver os resultados (a senha fica no campo `admin_senha` do `php/config.php`).

Abra sempre pelo endereço `http://localhost/...`. Se abrir o `index.html` com duplo clique, o questionário funciona, mas as respostas não são gravadas.

## Publicando em uma hospedagem com PHP e MySQL

Crie o banco pelo painel da hospedagem (o nome, o usuário e o host costumam ter prefixos, então copie exatamente o que o painel mostrar), execute o `database.sql` no phpMyAdmin dela, copie `php/config.example.php` para `php/config.php` com esses dados e uma senha forte para o admin, e envie os arquivos para a pasta pública do site.

## Cuidados

- Nunca envie o `php/config.php` nem exportações com nomes e e-mails para o GitHub.
- Nome e e-mail são dados pessoais (LGPD): o questionário pede autorização antes de começar, e o acesso ao admin deve ficar restrito a quem conduz o diagnóstico.
- A senha do admin é simples, pensada para aprendizado. Em uso real, prefira `password_hash`, HTTPS e limite de tentativas de login.
- Percentual de cada elemento = pontos ÷ (12 perguntas × 3) × 100.
