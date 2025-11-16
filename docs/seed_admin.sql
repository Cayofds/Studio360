-- Seed inicial para criar um administrador padrão.
-- Altere os valores conforme necessário antes de executar.
-- Senha padrão: Admin@123 (altere no primeiro acesso).
INSERT INTO `usuarios`
    (`usuario`, `email`, `senha`, `foto_perfil`, `nivel`, `cnpj`, `nome_real`)
VALUES
    ('admin', 'admin@studio360.com', '$2y$10$xxJPz212wOeI7eV2rzUHlOIcouFOgQui7be5ToW6q/cR556d1hF0K', '', 0, NULL, 'Administrador Padrão');
