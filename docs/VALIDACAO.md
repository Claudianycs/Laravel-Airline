# Validação da versão PHP 8.1.4 / Laravel 10

Verificações realizadas em 15/10/2026 após a adaptação:

- Runtime real PHP **8.1.4**, obtido da imagem oficial php:8.1.4-cli e executado com extensões PDO MySQL e pcntl. A versão mínima no composer.lock foi fixada em 8.1.4, sem ignorar requisitos.
- 32 testes funcionais aprovados com 238 assertions em SQLite e novamente em MySQL 8.4.
- 1 teste de concorrência aprovado no MySQL, com 8 assertions: duas vendas simultâneas para o último lugar resultaram em uma venda e uma recusa.
- Os dois testes adicionais verificam a preparação do .env, incluindo a preservação de senha vazia de MySQL existente e a repetibilidade da configuração.
- Composer check-platform-reqs e composer validate --strict aprovados no PHP 8.1.4.
- Autenticação, recuperação de senha pelo broker Laravel, permissões, CRUDs de todas as entidades, compra, reserva, cancelamento e relatório validados pelos testes.
- Migração do banco de desenvolvimento já existente: Nothing to migrate; seeders repetidos sem duplicação. O esquema SQL e as migrations de entidades permanecem compatíveis.
- Servidor HTTP iniciado com PHP 8.1.4; páginas de dashboard, entidades, passagens, venda e relatório verificadas com sessão autenticada. Requisição sem CSRF recusada com HTTP 419.
- MySQL e phpMyAdmin mantidos; pagamentos e envio de e-mail externo continuam simulados/logados.

## Compatibilidade e segurança

PHP 8.1 e Laravel 10 estão fora de suporte. Esta versão é destinada a testes locais e uso acadêmico com PHP 8.1.4, conforme solicitado.

A resolução do Composer informou quatro avisos de segurança em laravel/framework. O metadado oficial do Packagist identifica os seguintes avisos como aplicáveis à versão legada:

- PKSA-d5tc-s1qs-h781
- PKSA-3r5d-mb8f-1qw9
- PKSA-m5cs-t1y6-qpcs
- PKSA-mdq4-51ck-6kdq

Esses avisos não foram ignorados na configuração. A consulta detalhada de composer audit ao endpoint packagist.org ficou bloqueada pela política de rede deste ambiente; a contagem e os identificadores acima vieram da resolução e dos metadados oficiais já obtidos de repo.packagist.org. Não foi verificada a explorabilidade individual de cada aviso neste aplicativo.

Para publicação em produção, migre para versões de PHP e Laravel com suporte de segurança.
