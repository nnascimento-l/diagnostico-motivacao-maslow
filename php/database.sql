-- Rode este script na aba "SQL" do phpMyAdmin, com o banco "diagnostico" selecionado.

CREATE TABLE IF NOT EXISTS respondentes (
  id        INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nome      VARCHAR(150) NOT NULL,
  email     VARCHAR(150) NULL,
  setor     VARCHAR(100) NULL,
  total_v   TINYINT UNSIGNED NOT NULL,   -- Fisiológicas
  total_w   TINYINT UNSIGNED NOT NULL,   -- Segurança
  total_x   TINYINT UNSIGNED NOT NULL,   -- Sociais
  total_y   TINYINT UNSIGNED NOT NULL,   -- Auto-estima
  total_z   TINYINT UNSIGNED NOT NULL,   -- Realização
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS respostas (
  id             INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  respondente_id INT UNSIGNED NOT NULL,
  pergunta       TINYINT UNSIGNED NOT NULL,   -- 1 a 30
  elemento_a     CHAR(1) NOT NULL,
  pontos_a       TINYINT UNSIGNED NOT NULL,
  elemento_b     CHAR(1) NOT NULL,
  pontos_b       TINYINT UNSIGNED NOT NULL,
  CONSTRAINT fk_respostas_respondente
    FOREIGN KEY (respondente_id) REFERENCES respondentes (id) ON DELETE CASCADE,
  CONSTRAINT ck_soma_tres CHECK (pontos_a + pontos_b = 3)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
