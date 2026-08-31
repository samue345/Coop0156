# Sistema de Análise de Crédito Cooperativo (Coop0156)

## Algumas Considerações
No fluxo de criação de análise de crédito, um novo cliente deve ser criado caso ele não exista. Ele deve ser criado com os dados recebidos do formulário, que são nome, CPF e renda mensal como requisito. A questão é que, na migração de clientes, o email é not null. 

Pensando nisso, eu alterei a migration, deixando o email nullable. Entretanto, no CRUD de clientes, o email é obrigatório, então eu resolvi isso a nível de código, ou seja, no banco é nullable para manter os requisitos do fluxo de análise de crédito, porém eu deixo o email como obrigatório no CRUD de clientes.Então se a migration já rodou rode o comando:

```./vendor/bin/sail artisan migrate:fresh```

Além disso, Para não expor o ids no front-end eu usei um package do laravel chamado Hashids, que basicamente cria um hash do id, isso evita que ids fiquem expostos. Então o simulation/{id} vira simulation/{code}.  

## O que foi implementado
Eu implementei tudo o que estava no readme, todos os requisitos obrigatórios e diferenciais foram feitos. Fiz o CRUD completo de cliente com todas as validações, criei a tela de cadastro de clientes, criei a integração com o Bureau e implementei as regras de negócios. Na tela de simulação e contratação, fiz todos os itens obrigatórios, criei diversos testes, tanto os obrigatórios quanto uma quantidade considerável de não obrigatórios, fiz melhorias no front-end, como validações de formulário e refatoração, criei uma tela de listagens de contratações, implementei o ProcessContractingJob e fiz algumas melhorias de performance.

Abaixo eu explico com mais detalhes algumas implementações e explico os motivos pelos quais eu implementei dessa forma. 

### 1. CRUD de clientes
Eu criei os métodos de CRUD utilizando uma camada de Controller, Service e uma camada de Repository para separar as regras de negócio do acesso aos dados. Pensando no princípio da Inversão de Dependência do SOLID, o Service recebe o Repository por injeção de dependência, dependendo de uma interface em vez de uma implementação. O bind entre a interface e a implementação é registrado no AppServiceProvider. Assim, o código fica menos acoplado e, caso eu precise trocar a estratégia de persistência ou isolar melhor a regra de negócio do ORM, basta criar uma nova implementação do Repository mantendo o mesmo contrato. 

Eu criei todas as validações com form request (Uma alternativa interessante para o form request seria um pacote do spatie chamado Laravel data, porque é possível fazer as validações que o form request faz e além disso ele é um DTO, dessa forma eu consigo tipar os atributos, e fazer formatações) 
na criação de cliente, como a de nome obrigatório, email obrigatório, formato válido e único, renda mensal sendo obrigatório com número positivo, na válidação de CPF achei válido criar uma Rule "ValidCPF" porque eu faço a validação de CPF na criação e edição de cliente e também na criação de análise de crédito, e Além de validar se o CPF é obrigatório, possui 11 dígitos e é único, também verifico se ele é um CPF válido. 

Foram criadas as rotas de detalhe do cliente, responsável por retornar os dados de um cliente específico. Como a rota utiliza route model binding com suporte a Hashids, o parâmetro recebido é resolvido automaticamente para o model correspondente. Quando o código informado não representa um cliente válido ou existente, a aplicação retorna 404.

Também implementei a rota de atualização, mantendo as validações com FormRequest e tratando a unicidade de CPF e e-mail sem considerar o próprio registro editado. Por fim, criei a rota de remoção, que exclui o cliente informado e retorna 204 No Content quando a operação é concluída com sucesso.

Eu criei o front end da tela de cadastro de cliente, O formulário é validado no front-end para evitar requests denecessárias para o servidor e criei uma listagem paginada de clientes utilizando simplePaginate, pois ele não executa uma consulta adicional de COUNT(*) para calcular o total de registros, tendo melhor performance que o paginate. Também utilizei CustomerResource para padronizar o formato das respostas da API. As respostas expõem um código público do cliente via Hashids, evitando depender diretamente do id incremental do banco nas interações externas.


### 2. Integração com o Bureau e Regras de Negócio

implementei a integração com o Bureau de Crédito por meio de um client específico, o CreditBureauClient, responsável por consultar o score do cliente a partir do CPF. Para reduzir o acoplamento, a aplicação depende da interface CreditScoreProvider, e não diretamente da implementação concreta. Com isso, os services dependem apenas de um contrato, enquanto detalhes como HTTP, URL, timeout, tratamento de erro e formato da resposta ficam isolados na camada de integração. Essa decisão facilita manutenção e permite trocar futuramente o provedor de score criando uma nova implementação da mesma interface. 

Além disso apliquei rate limit na rota de solicitação de análise de crédito. Em routes/api.php, a rota POST /api/analise-credito usa o middleware throttle:credit-analysis, e o limite é configurado no AppServiceProvider com 

```Limit::perMinute(10)->by($request->ip()).```

Essa proteção evita excesso de chamadas por IP em um endpoint sensível, já que a solicitação de análise dispara consulta ao Bureau e executa as regras de elegibilidade.

As regras de negócio da análise foram centralizadas no domínio, dentro de app/Domain/CreditAnalysis. Então, criei a interface CreditRule, que define o contrato comum das regras com o método `evaluate`. Cada rule avalia uma parte específica da política de crédito e retorna uma decisão apenas quando precisa reprovar ou encerrar o fluxo. Quando a rule retorna null, o avaliador entende que o fluxo pode continuar para a próxima regra. O CreditEligibilityEvaluator é responsável por orquestrar essas regras em sequência. 

Ele recebe uma lista de rules por injeção de dependência, e percorre cada uma até encontrar uma decisão. Essa abordagem deixa cada critério de elegibilidade isolado em uma classe própria, facilitando manutenção e futuras alterações, porque dessa forma, não se faz necessário criar um if a cada regra nova que for criada, evitando assim a concentração de ifs em um método só.

As regras foram separadas por responsabilidade: MinimumIncomeRule que valida a renda mínima; MinimumScoreRule que reprova scores abaixo do limite permitido; InterestRateRule que define a taxa de juros conforme a faixa de score; InstallmentRule que calcula o valor da parcela; e IncomeCommitmentRule que valida se a parcela ultrapassa o percentual máximo de comprometimento da renda. Essa ordem é muito importante, porque algumas regras enriquecem o CreditContext com dados calculados, como taxa de juros e parcela, que são usados pelas regras seguintes.

Também extraí os valores fixos de negócio para constantes dentro das próprias rules, como renda mínima, score mínimo de aprovação, score para melhor taxa, taxas de juros, quantidade de parcelas e percentual máximo de comprometimento da renda. Fiz isso com a inteção de evitar números mágicos no código, isso ajuda quando outro desenvolvedor for ler e entender o código quando dar manutenção porque não vai ser necessário interpretar cálculos espalhados pela aplicação.


### 3. Tela de Simulação e Contratação

Na tela de simulação, implementei a integração do frontend com o fluxo real de contratação. Adicionei o JavaScript responsável por chamar o endpoint POST /api/analise-credito/{creditAnalysis}/contratar, controlar o estado de carregamento, exibir mensagens de erro e apresentar uma confirmação quando a contratação é solicitada com sucesso.

Na tela de simulação eu não criei nenhum service, só o SimulationController, já que ele só faz validação simples e chama uma view. Essa validação verifica se a análise pode ser exibida usando o método canBeViewedInSimulation() do enum AnalysisStatus. Essa regra permite visualizar apenas análises com status aprovado, processando_contratacao ou contratado, redirecionando análises pendentes ou reprovadas para a tela inicial.

No backend, a contratação é iniciada pelo método `startContracting()` do `CreditAnalysisService`. Antes de iniciar o processo, o `CreditAnalysisController` valida se a análise está com status `aprovado`; caso contrário, retorna `409 Conflict`, informando que a análise precisa estar aprovada para ser contratada. Na parte de update pensei que emn alguns casos pode ocorrer race condition, então quando a contratação é aceita, o service faz uma transição atômica, e condicional do status para `processando_contratacao`, garantindo que apenas uma requisição consiga iniciar a contratação. Em seguida, despacha o job `ProcessContractingJob`, que finaliza o fluxo atualizando a análise para `contratado`. Caso a análise deixe de estar disponível durante essa operação, a API também retorna `409 Conflict` e não despacha um novo job.

Criei a listagem de contratações para permitir que o usuário acompanhe quais análises estão em processando_contratacao e contratado. A consulta filtra apenas os status relevantes e carrega o relacionamento com o cliente usando eager loading com `with('customer')`, evitando o problema de N+1 consultas. Também utilizo eager loading nas consultas das listagens e `simplePaginate` para a paginação.


### 4. Testes automatizados 

<img width="383" height="104" alt="image" src="https://github.com/user-attachments/assets/ce3455b8-09db-4b8f-85e2-d254ec8d3ce0" />

Eu criei diversos testes, todos os testes obrigatórios, além de outros que eu achei necessário, acredito que os testes estão cobrindo uma parte consideravel do código.

Criei testes automatizados para cobrir os principais fluxos da aplicação, separando os cenários por responsabilidade. Nos testes de clientes, validei o CRUD completo da API, incluindo criação, listagem paginada, exibição, atualização e remoção. Também cobri cenários de validação, como campos obrigatórios, CPF inválido, CPF/e-mail duplicados e renda mensal negativa. A ideia foi garantir tanto o caminho feliz quanto os principais erros esperados da entrada de dados.

Para a análise de crédito, criei testes cobrindo o fluxo completo de solicitação, desde a criação ou reutilização do cliente até a persistência da análise e aplicação das regras de negócio. Os testes verificam os cenários de aprovação com score médio e alto, reprovação por renda mínima, reprovação por score baixo e reprovação por comprometimento de renda acima do limite permitido. Também validei se o CPF informado é realmente usado na consulta ao Bureau e se os tipos de crédito aceitos são respeitados.

Também criei testes unitários para as regras de domínio da análise de crédito, especialmente no CreditEligibilityEvaluator. A decisão que tomei foi testar as regras de negócio fora do contexto HTTP e banco de dados, garantindo que critérios como renda mínima, score mínimo, taxa aplicada, cálculo da parcela e limite de comprometimento de renda funcionem isoladamente.

Na integração com o Bureau, criei testes específicos para o CreditBureauClient utilizando Http::fake, validei o tratamento de respostas bem-sucedidas, erro do provedor, timeout, rate limit e payload inválido sem score. Além disso, adicionei testes de integração contra o endpoint real do mock Bureau, sem Http::fake, para garantir que os cenários simulados continuam compatíveis com o fluxo da aplicação.

Para contratação, criei testes garantindo que apenas análises aprovadas possam ser contratadas. Também validei que, ao contratar, o status passa para processando_contratacao e o job de processamento é despachado. Separei também testes para o ProcessContractingJob, garantindo que ele conclui a contratação ao alterar o status para contratado, ignora análises em status inválido e retorna para aprovado em caso de falha no processamento.

Além disso, cobri partes de segurança e infraestrutura da aplicação, como rate limit na solicitação de análise, headers de segurança nas rotas da API e nas páginas web, tratamento global de exceções de banco e exposição de identificadores públicos via Hashids.

Por fim, adicionei testes de disponibilidade da API como smoke tests. Eles não substituem os testes específicos de cada fluxo, mas ajudam a identificar rapidamente se endpoints principais deixaram de responder por erro de configuração, rota quebrada ou falha inesperada. A estratégia geral que usei foi combinar testes de feature, integração e unidade, cobrindo tanto comportamento de usuário quanto regras de domínio isoladas.

### 5. Melhorias de performance e segurança

Também fiz algumas melhorias de performance no frontend e no ambiente de execução com Sail.

No frontend, removi o uso do Tailwind via CDN e passei a carregar os assets com Vite usando app.js. Antes, algumas telas carregavam o Tailwind diretamente pelo navegador e definiam configurações de tema dentro da própria view. Com a mudança, o CSS passa a ser processado no build da aplicação, assim o Tailwind gera apenas as classes utilizadas nas views e nos arquivos js. 

Também defini o PHP_CLI_SERVER_WORKERS em 4 nas configurações do Sail em docker/sail-start.sh. Assim, chamadas para API, carregamento de páginas e assets não ficam tão facilmente bloqueados por uma única requisição em andamento. Essa melhoria é voltada ao ambiente local. Em produção, o correto seria usar outras estratégias. Além disso, criei dois middlewares: um para as rotas de API, que não protege a rota de mock do Bureau, e outro para as rotas web.

São middleware bem simples; a de API adiciona headers como X-Content-Type-Options, X-Frame-Options, Referrer-Policy, Permissions-Policy e Cache-Control. A intenção foi reduzir riscos comuns, como MIME sniffing, carregamento da aplicação em iframe, exposição de informações por referrer e uso indevido de permissões do navegador. 

A de adicionar os principais headers de segurança nas respostas HTML, mas sem o Cache-Control: no-store, que foi mantido apenas na API.  Essa separação evita tratar páginas web e respostas de API exatamente da mesma forma, permitindo ajustar a política de cache e
segurança conforme o tipo de resposta.
