
curpath=$(dirname $(readlink -f $0))

basepath=$(dirname $curpath)

dumpdir=$basepath"/dump"
symbol_gendir=$basepath"/symbol_gen"


day=`date "+%Y-%m-%d-%H-%M"`

srcdir=$1
todir=$2
package=$3
version=$4
stackdir=$5

symbols=`grep "^$package:$version$" /disk1/dump/packlist | wc -l`
symbols=`grep "^$package:$version$" $dumpdir"/packlist" | wc -l`
tmpdir="/disk1/tmp/$package"
tmpdir=$symbol_gendir"/temp/"$package
file_prefix=$package-$version
file_name=$file_prefix-${day}.tar.gz

if [ ! -d $todir ]
then
	mkdir -p $todir
fi

if [ ! -d $tmpdir ]
then
	mkdir -p $tmpdir
fi
cd $tmpdir/../

fc=`ls $srcdir | wc -l`

if [ $fc -gt 0 ]
then 
	mv $srcdir/* $tmpdir

	if [ $symbols -gt 0 ]
	then
		sh $dumpdir"/genStack.sh" $tmpdir $stackdir $package $version
	fi
	
	# pack
	tar -zcvf $file_name $package
	
	mv $file_name $todir
	
	sudo chown nginx:nginx $todir -R

	# bak files
	cur_day=`date "+%Y-%m-%d"`
	if [ ! -d $file_prefix-$cur_day ]
	then
		mkdir $file_prefix-$cur_day
	fi
	mv $tmpdir/* $file_prefix-$cur_day
fi
	
# delete files older than 30 days
del_day=`date -d '30 days ago' '+%Y-%m-%d'`

rm -rf $todir/${package}*-${del_day}*

# delete old tmp files 
del_day=`date -d '3 days ago' '+%Y-%m-%d'`
rm -rf ${package}*-$del_day
