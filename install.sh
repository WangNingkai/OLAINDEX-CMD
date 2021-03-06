#!/usr/bin/env bash
PATH=/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/usr/local/sbin:/usr/local/php/bin:~/bin
export PATH

#=================================================
#     ____  __    ___   ________  _______
#    / __ \/ /   /   | / ____/  |/  / __ \
#   / / / / /   / /| |/ /   / /|_/ / / / /
#  / /_/ / /___/ ___ / /___/ /  / / /_/ /
#  \____/_____/_/  |_\____/_/  /_/_____/
#	System Required: CentOS/Debian/Ubuntu
#	Description: OLAINDEX-CMD
#	Author: WangNingkai
#	Blog: https://imwnk.cn
#=================================================

Green_font_prefix="\033[32m" && Red_font_prefix="\033[31m" && Green_background_prefix="\033[42;37m" && Red_background_prefix="\033[41;37m" && Font_color_suffix="\033[0m"
Info="${Green_font_prefix}[Info]${Font_color_suffix}"
Error="${Red_font_prefix}[Error]${Font_color_suffix}"
Tip="${Green_font_prefix}[Tip]${Font_color_suffix}"
Ok="${Green_font_prefix} ok ${Font_color_suffix}"
Failed="${Red_font_prefix} failed ${Font_color_suffix}"

[[ $(id -u) != '0' ]] && { echo "${Error} You must be root to run this script"; exit 1; }

function wget_check(){
    which wget > /dev/null 2>&1
    if [[ $? == 0 ]]; then
        echo -e "wget : ${Ok} "
    else
        echo -e  "wget : ${Failed} " && exit 1
    fi
}

function git_check(){
    which git > /dev/null 2>&1
    if [[ $? == 0 ]]; then
        echo -e "git : ${Ok}"
    else
        echo -e  "git : ${Failed} " && exit 1
    fi
}

function php_check(){
    which php > /dev/null 2>&1
    if [[ $? == 0 ]]; then
        echo -e "php : ${Ok}"
    else
        echo -e  "php : ${Failed} " && exit 1
    fi
}

function php_ext_check(){
    php_exe_dir="/usr/local/php/bin/php"
    php_ok=`${php_exe_dir} -r "echo version_compare(PHP_VERSION,'7.1.3','ge');"`
    [[ ${php_ok} -ne 1 ]] && echo -e  "${Error} PHP Version must be more than 7.1.3 " && exit 1 || echo -e "PHP Version : ${Ok}"
    php_ext=(bcmath ctype fileinfo json mbstring openssl tokenizer xml)
    for ext in ${php_ext[@]}
    do
        [[ -z `${php_exe_dir} -m | grep ${ext}` ]] && echo -e  "PHP Extension ${ext} : ${Failed} " && exit 1 || echo -e "PHP Extension : ${ext} : ${Ok}"
    done
}

function php_func_check(){
    php_exe_dir="/usr/local/php/bin/php"
    php_func=(exec shell_exec)
    for func in ${php_func[@]}
    do
        [[ -n `${php_exe_dir} -i | grep disable_functions | grep ${func}` ]] && echo -e  "PHP Func ${func} : ${Failed} " && exit 1 || echo -e "PHP Func : ${func} : ${Ok}"
    done
}

function composer_check(){
    which composer > /dev/null 2>&1
    if [[ $? == 0 ]]; then
        echo -e "composer : ${Ok}"
    else
        echo -e  "composer : ${Failed} " && exit 1
    fi
}

function install_composer(){
    wget -c https://getcomposer.org/composer.phar -O /usr/local/bin/composer > /dev/null 2>&1
    chmod +x /usr/local/bin/composer
    if [[ -e "/usr/local/bin/composer" ]]; then
        echo -e "${Info} Composer installed successfully!"
    else
        echo -e "${Error} Composer install failed, Please try again!"
    fi

}

function aria2c_check(){
    which aria2c > /dev/null 2>&1
    if [[ $? == 0 ]]; then
        echo -e "aria2c : ${Ok} "
    else
        echo -e "aria2c : ${Failed}" && exit 1
    fi
    # wget -N --no-check-certificate https://raw.githubusercontent.com/ToyoDAdoubi/doubi/master/aria2.sh && chmod +x aria2.sh && bash aria2.sh
}

function init_install(){
    mkdir -p olaindex && cd olaindex
    git clone https://github.com/WangNingkai/OLAINDEX-CMD.git tmp  && mv tmp/.git . && rm -rf tmp && git reset --hard
    composer install
    chmod -R 755 storage
    chown -R www:www *
    cp .env.example .env
    cp storage/app/config.sample.json storage/app/config.json
    chmod 777 storage/app/config.json
    chmod +x olaindex
    ./olaindex install
}
echo -e "${Tip} Requirements Check"
wget_check
git_check
php_check
php_ext_check
php_func_check
composer_check
aria2c_check
echo -e "${Info} Requirements Check Done"
echo -e "${Tip} Start Install"
init_install



