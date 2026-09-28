# TimeClock

Aplicação web/PWA para registo de jornada de trabalho em campo, criada inicialmente para uso real no dia a dia de auditoria, mas evoluída para poder servir outros profissionais com rotina externa, visitas a lojas, controlo de horários e geração de folha mensal em PDF.

## Visão geral

O TimeClock foi desenhado para resolver um problema muito concreto: registar entrada, almoço, retorno, saída, lojas visitadas e observações do dia sem depender de folhas manuais, Excel ou registos dispersos.

A plataforma permite:

- fluxo diário guiado de marcações
- histórico com edição e exclusão lógica
- apagar dados completos de um dia
- lançamento manual por data
- relatório mensal com visualização tipo folha
- exportação em PDF
- envio por email
- PWA instalável
- autenticação por email/password
- autenticação com Google
- gestão de conta
- configuração de dados permanentes do relatório

## Público-alvo

O TimeClock é especialmente adequado para:

- auditores de campo
- promotores
- merchandisers
- supervisores em mobilidade
- técnicos externos
- freelancers que precisam de folha mensal organizada
- profissionais que visitam várias lojas ou clientes por dia

## Stack técnica

- PHP
- JavaScript
- HTML/CSS
- MariaDB
- XAMPP (desenvolvimento local)
- Hostinger (produção)
- PWA com service worker e manifest
- Google OAuth para login social

## Estrutura do projeto

```text
app/
  config/
  controllers/
  core/
  models/
  views/
database/
public/
  assets/
  uploads/
  manifest.json
  service-worker.js
storage/
vendor/

Funcionalidades principais
1. Operação do dia

Fluxo operacional guiado com as etapas:

Entrada
Início de almoço
Fim de almoço / Retorno
Saída final

Na segunda etapa, a app permite decidir entre:

almoço
finalizar jornada

Isto torna o fluxo flexível para dias em que não existe pausa de almoço.

2. Histórico

A área de histórico permite:

consultar por data
editar marcações
apagar registos individuais
reabrir um dia finalizado
apagar todos os dados de um dia
3. Lançamento manual

Página própria para inserir dados manualmente por data, com foco em mínimo esforço.

Campos principais:

Data
Lojas visitadas por sequência
Entrada
Almoço início
Almoço fim
Saída
Observação

Ideal para:

dias antigos
correções retroativas
situações em que o utilizador ficou sem telemóvel
preenchimento posterior de semanas anteriores
4. Relatórios mensais

Geração de folha mensal com:

horas por dia
sábados, domingos e feriados
observações
lojas
tipo de auditoria
cálculo de total mensal
exportação PDF
envio por email
5. Configurações

Área para definir dados permanentes do relatório, como:

nome da empresa
trabalhador(a)
email destino
local
matrícula
categoria
logo
dados do cabeçalho
6. Autenticação

A plataforma suporta:

login
logout
registo
recuperação de password
redefinição de password
conta do utilizador
login com Google
7. PWA

A aplicação pode ser instalada como app e usada de forma prática em mobile.

Inclui:

manifest
service worker
splash
ícones
instalação por navegador compatível
Fluxo operacional resumido
O utilizador entra na app
A app mostra a próxima etapa esperada
O utilizador confirma a marcação
A app grava e prepara automaticamente a próxima
No final do mês, os dados servem de base para o relatório mensal
Requisitos
Desenvolvimento local
Windows
PowerShell 7
XAMPP
PHP compatível com o projeto
MariaDB
Composer
Produção
alojamento com PHP e MariaDB
HTTPS
Hostinger ou equivalente
Configuração local
1. Clonar/copiar o projeto

Colocar em:
C:\xampp\htdocs\auditor-app

2. Criar .env

Exemplo local:

APP_NAME=Auditor App
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost/auditor-app/public

DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=auditor_app
DB_USER=root
DB_PASS=

MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME=Auditor App
MAIL_TO=

GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=http://localhost/auditor-app/public/auth/google/callback

3. Importar a base de dados

Importar o schema SQL e garantir que as tabelas principais existem.

4. Abrir no browser

http://localhost/auditor-app/public

Configuração de produção
.env de produção

Exemplo:

APP_NAME=Auditor App
APP_ENV=production
APP_DEBUG=false
APP_URL=https://timeclock.alexdevcode.com/auditor-app/public

DB_HOST=localhost
DB_PORT=3306
DB_NAME=SEU_DB
DB_USER=SEU_USER
DB_PASS=SUA_PASS

MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME=Auditor App
MAIL_TO=

GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=https://timeclock.alexdevcode.com/auditor-app/public/auth/google/callback

Rotas importantes
/
/login
/register
/forgot-password
/reset-password
/account
/auth/google
/auth/google/callback
/history
/history/edit
/history/delete
/history/delete-day
/history/reopen-day
/manual-entry
/manual-entry/store
/export/pdf
/report/monthly-preview
/report/monthly-download
/send/email
/settings
Boas práticas adotadas no projeto
alterações com backup
foco em reversibilidade
investigação antes da mudança
cuidado com cache, PWA e service worker
UX orientada ao fluxo real
sem complexidade desnecessária
Estado atual do produto

O TimeClock já cobre bem o ciclo principal de utilização:

operação diária
histórico e correção
lançamento manual
relatório mensal
PDF
PWA
login social
produção em Hostinger
Melhorias futuras sugeridas
múltiplos utilizadores com papéis/perfis
permissões por empresa/equipa
dashboard com indicadores
filtros avançados no histórico
exportação Excel/CSV
alertas automáticos
aprovações
auditoria de alterações
onboarding inicial mais guiado
gestão multiempresa
Licença / uso

Projeto criado inicialmente para uso próprio e profissional, podendo ser adaptado para comercialização futura, licenciamento individual ou oferta a colegas de área.

Autor

Desenvolvido por AlexDevCode.


---