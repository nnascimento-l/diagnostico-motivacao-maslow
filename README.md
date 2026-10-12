## Diagnóstico de Motivação no Trabalho

Aplicação web de diagnóstico corporativo baseada na hierarquia de necessidades de Maslow, desenvolvida como atividade de curricularização da disciplina **Gestão de Equipes.**

## O que o sistema faz

O colaborador responde 30 perguntas. Em cada uma há duas respostas possíveis (A e B) e ele distribui 3 pontos entre elas (3 e 0, 2 e 1, 1 e 2 ou 0 e 3); ao escolher os pontos de um lado, o outro é preenchido automaticamente. Cada resposta está ligada a um dos cinco elementos: Fisiológicas, Segurança, Sociais, Auto-estima e Realização.

Ao final, o colaborador vê o próprio resultado: tabela com os pontos e o percentual de cada elemento, gráfico de colunas e a principal motivação, com opção de imprimir ou salvar em PDF. As respostas são gravadas em um banco MySQL, e uma área de administração (com senha) mostra a média geral, a média por setor, o gráfico de cada respondente e permite exportar tudo para o Excel.

Cálculo: percentual do elemento = pontos obtidos ÷ (12 perguntas × 3) × 100.

## Tecnologias

HTML, CSS e JavaScript (questionário e gráficos), PHP (servidor) e MySQL/MariaDB (banco de dados), rodando no XAMPP.

## Estrutura

Na pasta principal ficam as páginas do visitante (index.html, style.css, script.js, grafico.js). Na pasta php/ ficam o salvar.php (grava as respostas), o conexao.php (conexão com o banco), o database.sql (criação das tabelas), o config.example.php (modelo de configuração) e a pasta admin/ (área de resultados).

## Como executar no computador

1. Instale o XAMPP e ligue o Apache e o MySQL.
2. Copie esta pasta para `C:\xampp\htdocs\diagnostico`.
3. No phpMyAdmin (http://localhost/phpmyadmin), crie o banco `diagnostico`, abra a aba SQL e execute o conteúdo de `php/database.sql`.
4. Copie `php/config.example.php` para `php/config.php` e preencha os dados (no XAMPP: usuário root, senha vazia) e uma senha para o admin.
5. Acesse o questionário em http://localhost/diagnostico/ e a área de resultados em http://localhost/diagnostico/php/admin/.

> **Aviso:** Abra sempre pelo endereço `http://localhost/...`. Abrindo o `index.html` com duplo clique, o questionário funciona, mas as respostas não são gravadas.

## Privacidade (LGPD)

O sistema coleta nome, e-mail (opcional), setor (opcional) e as respostas, e pede autorização do participante antes de começar. O acesso à área de administração deve ficar restrito a quem conduz o diagnóstico. O arquivo php/config.php (senhas) não é enviado ao repositório.

Senha do admin com password_hash e limite de tentativas, publicação com HTTPS e comparação entre aplicações feitas em datas diferentes.

## Telas

<p align="center">
  <strong>TELA INICIAL</strong><br>

  <img width="1917" height="1078" alt="Página Inicial" src="https://github.com/user-attachments/assets/95266936-6e67-4c5a-b24c-938322e68e84" />
  </p>


  <p align="center">
  <strong>TELA DE PERGUNTAS</strong><br>

  <img width="1917" height="1078" alt="Página de Pergunta" src="https://github.com/user-attachments/assets/aa10a954-56e6-4bd4-b7b4-449fe29ff8ce" />
  </p>

<p align="center">
  <strong>TELA DE RESULTADO</strong><br>

  <img width="1910" height="1078" alt="Página Resultado" src="https://github.com/user-attachments/assets/8be591a6-173e-47cb-a0fb-1ff7837b1feb" />
  </p>

<p align="center">
  <strong>TELA ADMIN</strong><br>

  <img width="1917" height="1073" alt="Página ADMIN" src="https://github.com/user-attachments/assets/fc88cae2-6889-415b-9d29-0de50ec8da6f" />
</p>
