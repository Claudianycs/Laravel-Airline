# Validação do projeto

Verificações realizadas no ambiente de desenvolvimento em 14/10/2026:

- PHP 8.4.26 e Laravel 12.70.0; dependências Composer limitadas à compatibilidade com PHP 8.2+.
- 30 testes funcionais aprovados, com 228 assertions, tanto em SQLite em memória quanto em MySQL 8.4.
- 1 teste de concorrência aprovado no MySQL, com 8 assertions: duas emissões simultâneas para uma aeronave com um único lugar produziram uma venda e uma recusa por lotação.
- CRUD completo de companhias, aeroportos, aeronaves, voos, passageiros e usuários, além do fluxo completo de passagens.
- Autenticação, recuperação de senha pelo broker Laravel, autorização por perfil e bloqueio de acesso a passagens de outros clientes.
- Acesso HTTP autenticado às telas principais, dashboard, venda e relatório; ausência de token CSRF retornou HTTP 419.
- phpMyAdmin autenticado com o usuário da aplicação, exibindo as sete tabelas de entidades.
- Navegador Chromium: assets e JavaScript carregados, menu de celular funcional, página pública sem overflow horizontal.
- Build Vite, validação Composer e compilação das views Blade aprovados.
- SQL importado em banco vazio: migrations reconhecidas e seeders executados.
- Instalação reutilizável executada e seeders repetidos sem duplicação.
- ZIP extraído em uma pasta limpa: dependências instaladas e os 30 testes funcionais aprovados novamente.
- Servidor interrompido e reiniciado com as instruções de inicialização salvas.

Cobrança, pagamentos e estornos são simulados. Envio externo de e-mails e integração com companhias/provedores não foram configurados; a recuperação utiliza notificações simuladas nos testes e o mailer de log no desenvolvimento.

A configuração de nuvem foi preparada para revisão; publicação e restauração em uma nova tarefa são operações separadas.
