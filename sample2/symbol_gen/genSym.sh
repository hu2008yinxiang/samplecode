curpath="/disk1/symbol_gen/"
curpath=$(dirname $(readlink -f $0))

basepath=$(dirname $curpath)

packlist="/disk1/dump/packlist"
packlist=$basepath"/dump/packlist"

symbolsdir="/disk1/dump/symbols/"
symbolsdir=$basepath"/dump/symbols/"
tmpdir="/disk1/symbol_gen/tmp"
tmpdir=$basepath"/symbol_gen/tmp"

sodirs="release spacewar mt xiyou dhpad shootzombies"

echo "begin:"
lock_file=$curpath'/sdfsdfasdf'
if [ -f $lock_file ]
then
	exit 1;
fi

touch $lock_file

sh $curpath/updatePacklist.sh

for d in `echo $sodirs`
do
	echo $d
	cd $curpath/$d;
	svn cleanup;
	svn up;
	for f in `cat update`
	do
		version=`echo $f | awk -F, '{print $1}'`
		package=`echo $f | awk -F, '{print $2}'`
		soinpack=`echo $f | awk -F, '{print $3}'`
		sopath=`echo $f | awk -F, '{print $4}'`

		packcount=`grep "^$package:$version$" $packlist | wc -l`
		[ ! -f $sopath ] && packcount=1
		if [ $packcount -eq 0 ]
		then
			cp $sopath $tmpdir/$soinpack
			dump_syms $tmpdir/$soinpack > $tmpdir/$soinpack.sym
			sympath="$symbolsdir/"`head -n 1 $tmpdir/${soinpack}.sym | awk '{print $NF"/"$(NF-1)}'`
                	[ ! -d $sympath ] && mkdir -p $sympath
                	cp $tmpdir/${soinpack}.sym $sympath
			echo $package:$version >> $packlist
		fi
	done
done

rm $lock_file
