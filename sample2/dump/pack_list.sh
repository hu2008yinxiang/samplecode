
curpath=$(dirname $(readlink -f $0))

basepath=$(dirname $curpath)

dumpdir=$basepath"/dump"
symbol_gendir=$basepath"/symbol_gen"


src_dir=/disk1/www/clog/data/uf/
src_dir="$basepath/web/data/uf/"

to_dir=/disk1/www/clog/data/zf/
to_dir="$basepath/web/data/zf/"

stack_dir=/disk1/www/clog/data/stack/
stack_dir="$basepath/web/data/stack/"

cd "$(dirname "$0")"

for package in `ls $src_dir`
do
	for version in `ls $src_dir/$package`
	do
		sh pack.sh $src_dir/$package/$version $to_dir/$package $package $version $stack_dir/$package/$version
	done
done
